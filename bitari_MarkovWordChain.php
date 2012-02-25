<?php

class bitari_MarkovWordChain extends bitari_MarkovChain #
{
	const _STATE_HEADER = 'bitari_MarkovWordChain#v1';

	public function add( $text )
	{
		if ( !is_string( $text ) ) {
			return false;
		}

		$text = preg_replace( '/\s+/', ' ', ltrim( $text ) . ' ' );
		return parent::add( $text );
	}

	public function generate()
	{
		$text = parent::generate();
		return substr( $text, 0, -1 );
	}

	protected function shift( &$text )
	{
		$len = strcspn( $text, ' ' ) + 1;
		$ret = substr( $text, 0, $len );
		$text = substr( $text, $len );
		if ( $text === false ) $text = '';
		return $ret;
	}
}
