<?php

class bitari_MarkovChain #
{
	protected $order = 0;
	protected $added = 0;
	protected $tokens = array();

	const _STATE_HEADER = 'bitari_MarkovChain#v1';

	function __construct( $arg, $options = array() )
	{
		if ( is_integer( $arg ) && $arg >= 0 ) {
			$this->order = $arg;
		} elseif ( is_string( $arg ) && substr( $arg, 0, strlen( self::_STATE_HEADER ) + 1 ) === self::_STATE_HEADER . '~' ) {
			if ( $this->set_state_string( $arg ) !== true ) {
				throw new Exception( 'Bad state-string in ' . __CLASS__ . ' constructor' );
			}
		} elseif ( is_string( $arg ) ) {
			if ( $this->load_state_file( $arg ) !== true ) {
				throw new Exception( 'Bad state-file in ' . __CLASS__ . ' constructor' );
			}
		} else {
			throw new Exception( 'Bad argument in ' . __CLASS__ . ' constructor' );
		}
	}

	public function get_state_string()
	{
		return self::_STATE_HEADER . '~' . $this->order . '~' . $this->added . '~' . serialize( $this->tokens );
	}

	protected function set_state_string( $arg = NULL )
	{
		if ( !is_string( $arg ) ) {
			return false;
		}

		$sep1 = strpos( $arg, '~' );
		if ( !is_integer( $sep1 ) ) {
			return false;
		}

		if ( substr( $arg, 0, $sep1 ) !== self::_STATE_HEADER ) {
			return false;
		}

		$sep2 = strpos( $arg, '~', $sep1 + 1 );
		if ( !is_integer( $sep2 ) ) {
			return false;
		}

		$new_order = intval( substr( $arg, $sep1 + 1, $sep2 - $sep1 - 1 ) );
		if ( $new_order < 0 ) {
			return false;
		}

		$sep3 = strpos( $arg, '~', $sep2 + 1 );
		if ( !is_integer( $sep3 ) ) {
			return false;
		}

		$new_added = intval( substr( $arg, $sep2 + 1, $sep3 - $sep2 - 1 ) );
		if ( $new_added < 0 ) {
			return false;
		}

		$new_tokens = unserialize( substr( $arg, $sep3 + 1 ) );
		if ( !is_array( $new_tokens ) ) {
			return false;
		}

		$this->order = $new_order;
		$this->added = $new_added;
		$this->tokens = $new_tokens;

		return true;
	}

	public function save_state_file( $filename )
	{
		return file_put_contents( $filename, $this->get_state_string() );
	}

	protected function load_state_file( $filename )
	{
		return $this->set_state_string( file_get_contents( $filename ) );
	}

	public function _dump()
	{
#		var_dump( $this->tokens );
		print_r( $this->tokens );
	}

	public function add_file( $filename, $splitter, $mangler = NULL )
	{
		$contents = file_get_contents( $filename, false );
		if ( !is_string( $contents ) ) {
			return false;
		}

		$strings = preg_split( $splitter, $contents );
		if ( !is_array( $strings ) ) {
			return false;
		}
		unset( $contents );

		$retval = true;
		foreach ( $strings as $string ) {
			if ( $mangler !== NULL ) {
				$string = call_user_func( $mangler, $string );
				if ( !is_string( $string ) ) {
					$retval = false;
				}
			}
			$this->add( $string );
		}
		return $retval;
	}

	public function add( $line )
	{
		if ( !is_string( $line ) ) {
			return false;
		}

		$hist = '';
		$histsize = 0;

		while ( $line !== '' ) {
			$next = $this->shift( $line );
			$this->add_token( $histsize, $hist, $next );
			$hist = $hist . $next;
			$histsize++;

			while ( $histsize > $this->order ) {
				$this->shift( $hist );
				$histsize--;
			}
		}

		while ( $histsize > 0 ) {
			$this->add_token( $histsize, $hist, '' );
			$this->shift( $hist );
			$histsize--;
		}

		return true;
	}

	protected function add_token( $histsize, $hist, $next )
	{
#		print "[$histsize][$hist][$next]\n";
		if ( array_key_exists( $histsize, $this->tokens ) ) {
			if ( array_key_exists( $hist, $this->tokens[$histsize] ) ) {
				if ( array_key_exists( 'SUM', $this->tokens[$histsize][$hist] ) ) {
					$this->tokens[$histsize][$hist]['SUM']++;
				} else {
					$this->tokens[$histsize][$hist]['SUM']=1;
				}
				if ( array_key_exists( $next, $this->tokens[$histsize][$hist] ) ) {
					$this->tokens[$histsize][$hist][$next]++;
				} else {
					$this->tokens[$histsize][$hist][$next]=1;
				}
			} else {
				$this->tokens[$histsize][$hist] = array( 'SUM' => 1, '' => 0, $next => 1 );
			}
		} else {
			$this->tokens[$histsize] = array( $hist => array( 'SUM' => 1, '' => 0, $next => 1 ) );
		}
		$this->added++;
	}

	public function generate()
	{
		$line = '';
		$hist = '';
		$histsize = 0;

		do {
			$rand = mt_rand( 0, $this->tokens[$histsize][$hist]['SUM'] - 1 );

			foreach ( $this->tokens[$histsize][$hist] as $next => $count ) {
				if ( $next === 'SUM' ) continue;
				if ( $rand < $count ) {
					break;
				}
				$rand = $rand - $count;
			}

			if ( $next === '' ) {
				return $line;
			}

			$line = $line . $next;
			$hist = $hist . $next;
			$histsize++;

			while ( $histsize > $this->order ) {
				$this->shift( $hist );
				$histsize--;
			}
		} while ( true );
	}

	protected function shift( &$text )
	{
		$len = 1;
		$ret = substr( $text, 0, $len );
		$text = substr( $text, $len );
		if ( $text === false ) $text = '';
		return $ret;
	}
}
