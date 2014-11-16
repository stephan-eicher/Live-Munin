<?php
/**
 * LiveMunin connects to munin-node and fetches config and values from 
 * given service at configured node.
 * 
 * The values are stored in the browser session (TODO: global stats-file?).
 *
 * Values can then be plotted with Flot (http://code.google.com/p/flot/)
 * which produces beautiful graphical plots of arbitrary datasets on the
 * fly. 
 *
 * Still needs some love regarding scaling of numbers etc. (max/min from
 * plugins, descriptions of values ..)
 *
 * Copyright 2010 Harald Nesland
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class LiveMunin {
    
    public $host = 'localhost';
    public $port = 4949;
    
    public $history = 30;
    
    public function __construct() {
        session_name('liveMunin');
        session_start();
    }
    
    public function config( $service ) {
        
        if( $cache = $this->cache( $service ) ) return $cache;
        
        $fp = fsockopen( $this->host, $this->port );
        fgets($fp);
        fwrite( $fp, "config $service\n");
        $parse = true;
        $values = Array();
        $types = array();
        while( $parse ) {
            $line = fgets( $fp );
            if( preg_match("/^(\S+)\.type\s(.*)/", trim($line), $m ) ) {
                $types[$m[1]] = $m[2];
            }
            if( $line == ".\n" ) $parse = false;
        }
        
        fclose( $fp );
        
        $this->cache( $service, $types );
        
        return $types;
    }
    
    public function fetch( $service ) {
        
        $fp = fsockopen( $this->host, $this->port );
        fgets($fp);
        fwrite( $fp, "fetch $service\n");
        $parse = true;
        $values = Array();
        while( $parse ) {
            $line = fgets( $fp );
            if( preg_match("/^(\S+)\.value\s(.*)/", trim($line), $m ) ) {
                $values[$m[1]] = $m[2];

            }

            if( $line == ".\n" ) $parse = false;
        }
        
        fclose( $fp );
        
        return $values;
    }
    
    public function services() {
        
        $fp = fsockopen( $this->host, $this->port );
        fwrite( $fp, "list\n");
        fgets( $fp );        
        $services = explode(' ', trim(fgets( $fp )));
        fclose( $fp );
        
        return $services;
    }
    
    public function cache( $key, $value = null ) {
        if( $value == null ) {
            if( isset( $_SESSION['c' . $key] ) ) return $_SESSION['c' . $key];
            return false;
        } else {
            $_SESSION['c' . $key] = $value;
        }
    } 
    
    public function queue( $key, $value ) {
        
        if( !isset( $_SESSION['d' . $key] ) ) {
            $_SESSION['d' . $key] = Array();
        }
        
        if( count( $_SESSION['d' . $key] ) > $this->history ) {
            array_shift( $_SESSION['d' . $key] );
        }
        
        array_push( $_SESSION['d' . $key], $value );
        
    }
    
    public function collect( $service ) {
        
        $config = $this->config( $service );
        $data = $this->fetch( $service );

        $results = Array();
        foreach( $data as $type => $value ) {
            $result = Array();
            if (!isset($config[$type])) {
                $config[$type] = '';
            }
            switch( $config[$type] ) {
                case 'DERIVE':
                    $oldval = $this->cache( 'old_' . $service . $type );
                    $result[$type] = $value - $oldval;
                    if( $result[$type] == $value ) $result[$type] = 0;
                    $this->cache( 'old_' . $service . $type, $value );
                break;
                case 'COUNTER':
                    $oldval = $this->cache( 'old_' . $service . $type );
                    $result[$type] = $value - $oldval;
                    if( $result[$type] == $value ) $result[$type] = 0;
                    $this->cache( 'old_' . $service . $type, $value );
                break;
                default:
                case 'GAUGE':
                    $result[$type] = $value;
                break;
            } 
            
            $results[$type] = array_values( $result );
        }

        return $results;
    }
    
    public function values( $service, $type ) {
        if( isset( $_SESSION['d' . $service . $type ] ) ) 
            return array_values( $_SESSION['d' . $service . $type ] );
        return array();
    }
    
    public function graphValues( $service ) {
        
        $data = $this->collect( $service );
        
        $sets = Array();
        foreach( $data as $type => $value ) {
            // Creates a set for Flot.
            // (time() + 3600) * 1000 converts current unix timestamp to JavaScript-timestamp
            $this->queue( $service . $type, Array( (time()+3600) * 1000, $value[0] ) );
            $serviceInfo = Array( 'label' => $type, 'data' => $this->values( $service, $type ) );
            $sets[] = $serviceInfo;
        }

        return json_encode( $sets );
    }
    
}
