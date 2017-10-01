<?php

# MantisScheduledTickets - a MantisBT (http://www.mantisbt.org) plugin

# MantisScheduledTickets is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisScheduledTickets is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisScheduledTickets.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Mantis Scheduled Tickets
 *
 * @package MantisScheduledTickets
 * @filesource
 * @copyright Copyright (C) 2015-2017 MantisScheduledTickets Team <support@mantis-scheduled-tickets.net>
 * @link http://www.mantis-scheduled-tickets.net
 */

$g_args = null;
$g_output_file_name = '';

$g_output_file = new SimpleXMLElement( '<?xml version="1.0"?><data />' );
$g_actions = $g_output_file->addChild( 'actions' );

/**
 * Initialize the script environment
 *
 * Initialize the script environment:
 * * parse command line arguments into an array
 * * validate command line arguments
 *
 * Example:
 *
 * script1.php is executed as follows:
 *   script1.php -a="IP" -c="credentials_file.txt" --output_file="/path/to/output/file"
 *
 * $p_given_args would contain the whole line above
 *
 * In order to initialize the environment, this script would call init_script as follows:
 *
 * init_script( $argv,
 *   array(
 *     '-a' => array( 'description' => 'IP address/FQDN of host to connect to', 'required' => true ),
 *     '-c' => array( 'description' => 'File that contains the credentials to be used', 'required' => false )
 *   )
 * );
 *
 * init_script() would parse the given command line arguments (i.e. $argv) and inspect its components
 * against the valid command line arguments defined in the second array. If an argument is defined as
 * required, but is missing from the command line, an error is thrown. If an argument is defined as
 * optional, processing would continue. If an argument is present on the command line but does not have
 * a corresponding entry in the second array, an error is thrown.
 *
 * The special arguments --diff-left-side and --output-file do NOT need to be explicitly specified in
 * the array. They will be defined as:
 *
 * array(
 *   '--output-file' => array( 'description' => 'The name (full path) of the output file', 'required' => true )
 *   '--diff-left-side' => array( 'description' => '[optional] File containing the previous execution\'s command output', 'required' => false ),
 * );
 *
 * @param mixed $p_given_args Command line arguments that were passed to the script
 * @param mixed $p_valid_args Array of valid command line arguments for this script
 * @return mixed Parse/validation results
 */
function init_script( $p_given_args, $p_valid_args = null ) {
    global $g_output_file_name;
    global $g_args;

    if( false == is_array( $p_valid_args ) ) {
        $p_valid_args = array();
    }

    # look for the special --diff-left-side and --output-file args
    # if not explicitly specified, add them to the list of valid arguments now
    if( false == array_key_exists( '--output-file', $p_valid_args ) ) {
        $p_valid_args['--output-file'] = array( 'description' => 'The name (full path) of the output file', 'required' => true );
    }

    if( false == array_key_exists( '--diff-left-side', $p_valid_args ) ) {
        $p_valid_args['--diff-left-side'] = array( 'description' => '[optional] File containing the previous execution\'s command output', 'required' => false );
    }

    # parse out the given command line arguments
    if( is_array( $p_given_args ) ) {
        foreach( $p_given_args as $t_idx => $t_arg ) {
            if( $t_idx > 0 ) {
                if( false !== strpos( $t_arg, '=' ) ) {
                    list( $arg_name, $arg_value ) = explode( '=', $t_arg );
                    $g_args[$arg_name] = $arg_value;
                } else {
                    $g_args[$t_idx] = null;
                }
            }
        }
    }

    $t_errors = null;

    # compare $g_args to $p_valid_args; if any unknown arguments have been
    # been passed in, throw an error and bail out
    if( $g_args ) {
        foreach( array_keys( $g_args ) as $t_arg_name ) {
            if( false == array_key_exists( $t_arg_name, $p_valid_args ) ) {
                $t_errors[] = "Invalid argument '$t_arg_name'\n";
            }
        }
    }

    # traverse $p_valid_args and ensure that all required parameters
    # have been passed in
    foreach( $p_valid_args as $t_arg_name => $t_arg_options ) {
        if( is_array( $t_arg_options ) &&
            isset( $t_arg_options['required'] ) &&
            $t_arg_options['required'] &&
            ( ( false == is_array( $g_args ) || ( false == array_key_exists( $t_arg_name, $g_args ) ) ) ) ) {
            $t_errors[] = "Missing required argument '$t_arg_name'\n";
        }
    }

    # If errors have been encountered, output those errors and exit
    if( null != $t_errors ) {
        process_errors( $t_errors, $g_args, $p_valid_args );
    }

    $g_output_file_name = $g_args['--output-file'];
}

/**
 * Process errors
 *
 * If an output file was specified, populate it with the errors encountered
 * and pass it back to the plugin. If an output file was NOT specified,
 * simply stop the execution of the script.
 *
 * @param mixed $p_errors Command line argument errors
 * @param mixed $p_args Parsed command line arguments
 * @param mixed $p_valid_args Valid command line arguments
 * @return void
 */
function process_errors( $p_errors, $p_args = null, $p_valid_args = null ) {
    global $g_actions;
    global $g_output_file_name;

    # try to get the name of the output file
    if( is_array( $p_args ) && isset( $p_args['--output-file'] ) && ( '' != $p_args['--output-file'] ) ) {
        $g_output_file_name = $p_args['--output-file'];
    }

    # ensure that we have a valid output file name
    if( '' == trim( $g_output_file_name ) ) {
        echo "No output file specified, exiting...";
        die;
    }

    # start listing the errors encountered
    $t_note = "Errors:\n";

    foreach( $p_errors as $t_error ) {
        $t_note .= $t_error . "\n";
    }

    # if $p_valid_args was given, show the correct usage
    if( is_array( $p_valid_args ) ) {
        $t_note .= "\nUsage:\n\n";

        foreach( $p_valid_args as $t_arg_name => $t_arg_options ) {
            $t_note .= "  $t_arg_name\n" . $t_arg_options['description'] . "\n\n";
        }
    }

    # add a note to the output file
    $g_actions->addChild( 'note', $t_note );

    create_output_file();
    die;
}

/**
 * Parse credentials out of the specified credentials file
 *
 * This function expects the second argument to be an array containing the specific pieces of information to be parsed
 * out of the credentials file.
 *
 * The file should be a collection of lines of the form:
 *   <name>=<value>
 *
 * Lines starting with a "#" are treated as comments and, therefore, ignored for parsing purposes.
 *
 * @param string $p_credentials_file Credentials file
 * @param array $p_credentials Array containing the specific pieces of information to extract from a credentials file
 * @return mixed Boolean false if any errors are encountered, associative array containing the parsed values that
 *               correspond to the given tokens otherwise
 */
function parse_credentials( $p_credentials_file, $p_credentials ) {
    # ensure that we have a valid file
    if( false == file_exists( $p_credentials_file ) ) {
        $t_errors[] = 'Credentials file not found';
        process_errors( $t_errors );
    }

    # if no tokens were specified, bail out
    if( false == is_array( $p_credentials ) ) {
        return false;
    }

    $t_parsed_credentials = parse_ini_file( $p_credentials_file );

    # if the array is not populated, something went wrong
    if( false == is_array( $t_parsed_credentials ) ) {
        $t_errors[] = 'None of the specified tokens could be found in the credentials file';
        process_errors( $t_errors );
    } else {
        # we may still be missing some items, look for those now
        $t_errors = null;

        foreach( $p_credentials as $t_credential ) {
            if( false == isset( $t_parsed_credentials[$t_credential] ) ) {
                $t_errors[] = $t_credential;
            }
        }

        if( is_array( $t_errors) ) {
            process_errors(
                array( 'err' => 'The following tokens are missing from the credentials file:' ) +
                $t_errors
            );
        }
    }

    # if we get to this point, we were able to parse out all of the given tokens
    return $t_parsed_credentials;
}

/**
 * Create output file
 *
 * @return void
 */
function create_output_file() {
    global $g_output_file;
    global $g_output_file_name;

    file_put_contents( $g_output_file_name, $g_output_file->asXML() );
}

/**
 * Create a new 'actions' node
 *
 * @return SimpleXMLElement New actions node
 */
 function create_new_actions_node() {
     global $g_output_file;

     return $g_output_file->addChild('actions');
 }
