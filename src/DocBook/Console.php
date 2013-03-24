<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook;

use \Markdown\Parser, \Markdown\ExtraParser;

/**
 * To use this command line interface, just run:
 *    ~$ php markdown.php --help
 */
class Console 
{

    protected $stdout; //stdout
    protected $stdin;  //stdin
    protected static $emd_instance; // PHP Extended Markdown instance
    protected $md_content='';
    protected $template_content='';
    protected $md_parsed_content='';

    /**#@+
     * Command line options values
     */
    protected $input         =array();
    protected $verbose       =false;
    protected $quiet         =false;
    protected $output        =false;
    protected $template      =false;
    protected $multi         =false;
    protected $config        =false;
    protected $filter_html   =false;
    protected $filter_styles =false;
    protected $nofilter      =false;
    protected $extract       =false;
    /**#@-*/

    /**#@+
     * Command line options
     */
    protected $options;
    static $cli_options = array(
        'v'=>'version', 
        'h'=>'help', 
        'x'=>'verbose', 
        'q'=>'quiet', 
        'o:'=>'output:', 
        't:'=>'template:', 
        'm'=>'multi', 
        'c:'=>'config:', 
        'g::'=>'gamuts::', 
        'filter-html', 
        'filter-styles', 
        'nofilter:', 
        'extract::'
    );
    /**#@-*/

    /**
     * Internal counter
     */
    static $parsedfiles_counter=1;

    const TEMPLATE_CONTENT_TAG = '@MD_CONTENT@';
    const TEMPLATE_TITLE_TAG = '@MD_TITLE@';

    /**
     * Constructor
     * Setup the input/output, verify that we are in CLI mode and that something is requested
     * @see self::runOptions()
     */
    public function __construct()
    {
        $this->stdout = fopen('php://stdout', 'w');
        $this->stdin = fopen('php://stdin', 'w');
        if (php_sapi_name() != 'cli') {
            exit('<!-- NOT IN CLI -->');
        }
        self::getOptions();
        if (empty($this->options) && empty($this->input)) {
            self::error( "No argument found - nothing to do!" );
        }

        // handle the Apache configuration if so
        if (isset($_SERVER['EMD_TPL']) && !empty($_SERVER['EMD_TPL'])) {
            $tpl_path = realpath(getenv("DOCUMENT_ROOT").$_SERVER['EMD_TPL']);
            if (!empty($tpl_path)) {
                self::runOption_template($tpl_path);
            }
        }

        // handle CLI options
        self::runOptions();
    }

// -------------------
// Writing methods
// -------------------

    /**
     * Write an info to CLI output
     * @param string $str The information to write
     * @param bool $new_line May we pass a line after writing the info
     */
    protected function write($str, $new_line = true)
    {
        fwrite($this->stdout, $str.( $new_line===true ? PHP_EOL : '' ));
        fflush($this->stdout);
    }
    
    /**
     * Write an info in verbose mode
     * @param string $str The information to write
     * @param bool $new_line May we pass a line after writing the info
     */
    protected function info($str, $new_line = true)
    {
        if ($this->verbose===true) self::write( ". ".$str." ...", $new_line );
    }
    
    /**
     * Write an error info and exit
     * @param string $str The information to write
     * @param int $code The error code used to exit the script
     */
    protected function error($str, $code = 1)
    {
        if ($this->quiet===true) {
            self::write( $str );
        } else {
            self::write( PHP_EOL.">> ".$str.PHP_EOL );
            self::write( "( run '--help' option to get information )" );
        }
        if ($code>0) {
            self::endRun();
            exit($code);
        }
    }
    
    /**
     * Write an info and exit
     * @param bool $exit May we have to exit the script after writing the info?
     * @param string $str The information to write
     */
    private function endRun($exit = false, $str = null)
    {
        if ($this->quiet===true) ini_restore('error_reporting'); 
        if (!empty($str)) self::write( $str );
        if ($exit==true) exit(0);
    }

// -------------------
// Options
// -------------------

    /**
     * Get the command line user options
     */
    protected function getOptions()
    {
        $this->options = getopt(
            join('', array_keys(self::$cli_options)),
            array_values(self::$cli_options)
        );

        $argv = $_SERVER['argv'];
        $last = array_pop($argv);
        while ($last && count($argv)>=1 && $last[0]!='-' && !in_array($last,$this->options)) {
            $this->input[] = $last;
            $last = array_pop($argv);
        }
        $this->input = array_reverse($this->input);
    }

    /**
     * Run the command line options of the request
     */
    protected function runOptions()
    {
        foreach($this->options as $_opt_n=>$_opt_v) {
            $opt_torun=false;
            foreach(array($_opt_n, $_opt_n.':', $_opt_n.'::') as $_opt_item) {
                if (array_key_exists($_opt_item, self::$cli_options)) {
                    $opt_torun = self::$cli_options[$_opt_item];
                } elseif (in_array($_opt_item, self::$cli_options)) {
                    $opt_torun = $_opt_n;
                }
            }
            $_opt_method = 'runOption_'.str_replace(':', '', str_replace('-', '_', $opt_torun));
            if (method_exists($this, $_opt_method)) {
                $ok = $this->$_opt_method( $_opt_v );
            } else {
                if (count($this->options)==1) {
                    self::error( "Unknown argument '$_opt_n'!" );
                } else {
                    self::info( "Unknown argument '$_opt_n'! (argument ignored)" );
                }
            }
        }

        if (!empty($this->input)) {
            if ($this->multi===true) {
                self::info( "Input files are setted on `".join(', ', $this->input)."`" );
            } else {
                self::info( "Input file is setted on `{$this->input[0]}`" );
            }
        }
    }

// -------------------
// CLI methods
// -------------------

    /**
     * Run the whole script depending on options setted
     */
    public function run()
    {
        if ($this->verbose===true) {
            self::write( PHP_EOL.">>>> let's go for the parsing ...".PHP_EOL );
        }
        if (!empty($this->input)) {
            if ($this->multi===true) {
                $myoutput = $this->output;
                foreach($this->input as $_input) {
                    if (!empty($this->output))
                        $this->output = self::buildOutputFilename( $myoutput );
                    $_ok = self::runStoryOnOneFile($_input);
                }
                if ($this->verbose===true)
                    self::write( "  -------------------------------------------" );
            } else {
                $_ok = self::runStoryOnOneFile($this->input[0]);
            }
        } else {
            self::error( "No input markdown file entered!" );
        }
        if ($this->verbose===true)
            self::write( PHP_EOL.">>>> the parsing is complete.".PHP_EOL );
        self::endRun(1);
    }

    /**
     * Get the help string
     */
    protected function runOption_help()
    {
//  --filter-html           filter out raw HTML (except styles)
//  --filter-styles         filter out HTML styles
//   -c | --config=FILE      configuration file to use for Markdown instance (INI format)
//   -g | --gamuts[=NAME]    get the list of gamuts (or just one if specified) processed on Markdown input
//   --nofilter=A,B          specify a list of filters that will be ignored during Markdown parsing
//   --extract[=META]        extract some (specific if sepcified) metadata from the Markdown input
        $help_str = <<<EOT
[ PHP Extended Markdown - command line interface ]

Usage:
  ~$ php path/to/emd_console [OPTION ...] [INPUT FILE(S) OR STRING(S)]

Options:
  -v | --version          get Markdown version information
  -h | --help             get this help information
  -x | --verbose          increase verbosity of Markdown
  -q | --quiet            do not write Markdown Parser or PHP error messages
  -m | --multi            multi-files input
  -o | --output=FILE      specify a file to write generated content
  -t | --template=FILE    specify a template file used to embed parsed content (replacing tag @MD_CONTENT@)

Converts text(s) in specified file(s) (or stdin) from markdown syntax.
By default, result is written through stdout in HTML format.

EOT;
        if (version_compare(PHP_VERSION, '5.1.0', '<')) {
            $phpvers = phpversion();
            $help_str .= <<<EOT

IMPORTANT NOTE: 
  Your system is running PHP $phpvers ; command line options with argument separated by
  an equal sign `=` is not supported ; please DO NOT USE EQUAL SIGN for options, use space :
    ~$ php path/to/emd_console -o my_output_file.html input_markdown.md

EOT;
        }
        self::write( $help_str );
        self::endRun();
        exit(0);
    }

    /**
     * Run the version option
     */
    protected function runOption_version()
    {
        self::write( 'MARKDOWN version: '.MARKDOWN_VERSION );
        self::write( 'MARKDOWN EXTRA version: '.MARKDOWNEXTRA_VERSION );
        self::endRun();
        exit(0);
    }

    /**
     * Run the verbose option
     */
    protected function runOption_verbose()
    {
        $this->verbose = true;
        self::info( "Enabling 'verbose' mode" );
    }

    /**
     * Run the quiet option
     */
    protected function runOption_quiet()
    {
        $this->quiet = true;
        error_reporting(0); 
        self::info( "Enabling 'quiet' mode, no PHP error will be written" );
    }

    /**
     * Run the multi option
     */
    protected function runOption_multi()
    {
        $this->multi = true;
        self::info( "Enabling 'multi' input mode" );
    }

    /**
     * Run the output option
     */
    protected function runOption_output($file)
    {
        $this->output = $file;
        self::info( "Setting 'output' on `$this->output`, parsed content will be written in file(s)" );
    }

    /**
     * Run the template option
     */
    protected function runOption_template($file)
    {
        $this->template = $file;
        self::info( "Setting 'template' on `$this->template`, parsed content will be embedded in template content" );
    }

    /**
     * Run the config file option
     */
    protected function runOption_config($file)
    {
        $this->config = $file;
        self::info( "Setting Markdown config file on `$this->config`" );
    }

    /**
     * Run the HTML filter option
     */
    protected function runOption_filter_html()
    {
        $this->filter_html = true;
        self::info( "Enabling HTML filter, all HTML will be parsed" );
    }

    /**
     * Run the styles filter option
     */
    protected function runOption_filter_styles()
    {
        $this->filter_styles = true;
        self::info( "Enabling HTML styles filter, will try to parse styles" );
    }

    /**
     * Run the extract option
     */
    protected function runOption_extract($meta = true)
    {
        $this->extract = is_bool($meta) ? true : $meta;
        if ($this->extract===true)
            self::info( "Enabling 'extract' mode, only metadata will be parsed" );
        else
            self::info( "Setting 'extract' on `$this->extract`, only this metadata will be parsed" );
    }

    /**
     * Run the no-filter option
     */
    protected function runOption_nofilter($str)
    {
        $this->nofilter = explode(',', $str);
        self::info( "Setting 'nofilter' on `".join(', ', $this->nofilter)."`, these will be ignored during parsing" );
    }

    /**
     * Run the gamuts option : list gamuts pile of the parser
     */
    protected function runOption_gamuts($name = null)
    {
        if (empty($name))
            self::info( "Getting lists of Gamuts from Markdown parser with current config" );
        else
            self::info( "Getting $name list of Gamuts from Markdown parser with current config" );
        $_emd = $this->getEmdInstance();
        $str='';
        if (!empty($name)) {
            $gamut = $_emd->getOption($name);
            $gamuts = array( $name );
            if (empty($gamut)) {
                $gamut = $_emd->getOption($name.'Gamut');
                $gamuts = array( $name.'Gamut' );
                if (empty($gamut)) {
                    self::error( "Unknown Gamut '$name'!" );
                }
            }
        } else {
            $gamuts = $_emd->getOption('gamutsList');
        }
        $max_td_length=30;
        foreach($gamuts as $_pile_name) {
            $str .= $_pile_name.PHP_EOL;
            $table = $_emd->getOption( $_pile_name );
            foreach($table as $_gamut_name=>$priority) {
                $str .= '    '.str_pad( $_gamut_name, $max_td_length ).'    '.$priority.PHP_EOL;
            }
        }
        self::write( $str );
        self::endRun();
        exit(0);
    }

// -------------------
// Process
// -------------------

    /**
     * Use of the PHP Extended Markdown class as a singleton
     */
    protected function getEmdInstance()
    {
        if (empty(self::$emd_instance)) {
            $_options = array(
                'skip_filters'=>$this->nofilter
            );
            if (false!==$this->config) {
                $_options['config_file'] = $this->config;
            }
            self::info( "Creating a PHP Extended Markdown instance with options ["
                .str_replace("\n", '', var_export($_options,1))
                ."]", false );
            self::$emd_instance = new \Markdown\ExtraParser( $_options );
        }
        return self::$emd_instance;
    }
    
    protected function runStoryOnOneFile($input)
    {
        if ($this->extract!==false) {
            $infos = self::runOneFile( $input, null, $this->extract );
            if ($this->quiet!==true)
                self::endRun(0, ">> Infos extracted from input `$input`"
                    .( is_string($this->extract) ? " for tag `$this->extract`" : '' )
                    .' : '.$infos);
            else
                self::endRun(0, $infos);
            return $infos;
        } elseif (!empty($this->output)) {
            $fsize = self::runOneFile( $input, $this->output );
            if ($this->quiet!==true)
                self::endRun(0, ">> OK - File `$this->output` ($fsize) written with parsed content from file `$input`");
            return $fsize;
        } else {
            $clength = self::runOneFile( $this->input[0] );
            return $clength;
        }
    }

    protected function runOneFile($input, $output = null, $extract = null)
    {
        $return=null;
        if (!empty($input)) {
            $num = self::$parsedfiles_counter;
            if ($this->verbose===true)
                self::write( "  -------------------------------------------" );
            self::info( "[$num] >> parsing file `$input`" );
            if ($md_content = self::getInput( $input )) {
                if (!is_null($extract)) {
                    $return = self::extractFromContent( $md_content, $extract );
                } else {
                    if ($md_parsed_content = self::parseContent( $md_content, $input )) {
                        if (!empty($output)) {
                            $return = self::writeOutputFile( $md_parsed_content, $output );
                        } else {
                            $return = self::writeOutput( $md_parsed_content );
                        }
                    }
                }
            }
            self::$parsedfiles_counter++;
        }
        return $return;
    }

    protected function getInput($input)
    {
        $md_content=null;
        if (!empty($input)) {
            if (@file_exists($input)) {
                self::info( "Reading input file `$input`", false );
                if ($md_content = @file_get_contents( $input )) {
                    $this->md_content .= $md_content;
                    self::info( "OK [strlen: ".strlen($md_content)."]" );
                } else {
                    self::error( "Could not open input file `$input`!" );
                }
            } else {
                self::error( "Entered input markdown file `$input` not found!" );
            }
        }
        return $md_content;
    }

    protected function getTemplate($input)
    {
        $md_content=null;
        if (!empty($input)) {
            if (@file_exists($input)) {
                self::info( "Reading template file `$input`", false );
                if ($md_content = @file_get_contents( $input )) {
                    self::info( "OK [strlen: ".strlen($md_content)."]" );
                    if (false!==strpos($md_content, self::TEMPLATE_CONTENT_TAG)) {
                        $this->template_content = $md_content;
                    } else {
                        self::error( "Template contents doesn't contain the required tag `".self::TEMPLATE_CONTENT_TAG."`!" );
                        $this->template_content = self::TEMPLATE_CONTENT_TAG;
                    }
                } else {
                    self::error( "Could not open template file `$input`!" );
                }
            } else {
                self::error( "Entered template file `$input` not found!" );
            }
        }
        return $md_content;
    }

    protected function parseContent($md_content, $input='')
    {
        $md_output=null;
        if (!empty($md_content)) {
            self::info( "Parsing Mardkown content", false );
            $_emd = $this->getEmdInstance();
            if ($md_output = $_emd->transform( $md_content )) {
                self::info("OK [strlen: ".strlen($md_output)."]");
                if (!empty($this->template)) {
                    if (empty($this->template_content))
                        $this->getTemplate($this->template);
                    self::info( "Including Mardkown content in template", false );
                    $ctt = str_replace(self::TEMPLATE_CONTENT_TAG, $md_output, $this->template_content);
                    $ctt = str_replace(self::TEMPLATE_TITLE_TAG, $input, $ctt);
                    $md_output = $ctt;
                    self::info("OK [strlen: ".strlen($md_output)."]");
                    $this->md_parsed_content .= $md_output;
                } else {
                    $this->md_parsed_content .= $md_output;
                }
            } else {
                self::error( "An error occured while trying to parse Markdown content ! (try to run `cd dir/to/markdown.php ...`)" );
            }
        }
        return $md_output;
    }

    protected function extractFromContent($md_content, $extract)
    {
        $md_output=null;
        if (!empty($md_content)) {
            self::info( "Extracting Mardkown metadata", false );
            $_emd = $this->getEmdInstance();
            if ($md_output = $_emd->transform( $md_content )) {
                $metadata = $_emd->get('metadata');
                self::info("OK [entries: ".count($metadata)."]");
                $md_output = '';
                foreach($metadata as $_metan=>$_metav) {
                    if (is_string($extract)) {
                        if ($extract==$_metan) $md_output = $_metav;
                    } else {
                        $md_output .= PHP_EOL.$_metan.' : '.$_metav;
                    }
                }
            } else {
                self::error( "An error occured while trying to extract data form Markdown content ! (try to run `cd dir/to/markdown.php ...`)" );
            }
        }
        return $md_output;
    }

    protected function writeOutputFile($output, $output_file)
    {
        $fsize=null;
        if (!empty($output) && !empty($output_file)) {
            self::info( "Writing parsed content in output file `$output_file`", false );
            if ($ok = @file_put_contents( $output_file, $output )) {
                $fsize = self::getFileSize( $output_file );
                self::info( "OK [file size: $fsize]" );
            } else {
                self::error( "Can not write output file `$output_file` ! (try to run `sudo ...`)" );
            }
        }
        return $fsize;
    }

    protected function writeOutput($output, $exit = false)
    {
        $clength=null;
        if (!empty($output)) {
            $clength = strlen($output);
            self::info( "Rendering parsed content [strlen: $clength]" );
            if ($this->verbose===true)
                self::write( "  -------------------------------------------" );
            self::write( $output );
        }
        return $clength;
    }

// ----------------------
// Utilities
// ----------------------

    protected static function getFileSize($file)
    {
        $size = @filesize($file);
        if (empty($size)) return null;
        if ($size < 1024) {
          return $size .' B';
        } elseif ($size < 1048576) {
          return round($size / 1024, 2) .' KiB';
        } elseif ($size < 1073741824) {
          return round($size / 1048576, 2) . ' MiB';
        } else {
          return round($size / 1073741824, 2) . ' GiB';
        }
    }

    protected function buildOutputFilename($filename)
    {
        $ext = strrchr($filename, '.');
        $_f = str_replace($ext, '', $filename);
        return $_f.'_'.self::$parsedfiles_counter.$ext;
    }

}

// Endfile
