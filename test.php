<?php

header( 'Content-Type: text/plain; charset=utf-8' );

require 'bitari_MarkovChain.php';
require 'bitari_MarkovWordChain.php';

ini_set( 'memory_limit', '1G' );

#$markov = new bitari_MarkovWordChain( 2 );
#$markov->add( 'It was a dark and stormy night; the rain fell in torrents -- except at occasional intervals, when it was checked by a violent gust of wind which swept up the streets (for it is in London that our scene lies), rattling along the housetops, and fiercely agitating the scanty flame of the lamps that struggled against the darkness.' );
#$markov->add_file( 'naked.txt', '/(\r?\n){2,}/' );
#$markov->add_file( 'magic.txt', '/(\r?\n){2,}/' );
#$markov->add_file( 'great.txt', '/(\r?\n){2,}/' );
#$markov->add_file( 'bible.txt', '/(\r?\n){2,}/', function ( $text ) { return preg_replace( '/[0-9]+:[0-9]+\s+/', ' ', $text ); } )
#$markov->_dump();
#$markov->save_state_file( 'state.dat' );
#exit;

$markov = new bitari_MarkovWordChain( 'state3.dat' );
print $markov->generate() . "\n";
