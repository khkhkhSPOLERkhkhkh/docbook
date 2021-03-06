<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Abstracts;

use DocBook\Abstracts\AbstractController,
    DocBook\Abstracts\AbstractPage,
    DocBook\Locator,
    DocBook\Response,
    DocBook\Request,
    DocBook\TemplateBuilder;

use Library\Command;

use MarkdownExtended\MarkdownExtended;

use Patterns\Abstracts\AbstractSingleton,
    Patterns\Commons\ConfigurationRegistry;

/**
 */
abstract class AbstractFrontController extends AbstractSingleton
{

    // dependences
    protected $routing = array();
    protected $locator;
    protected $registry;
    protected $request;
    protected $response;
    protected $controller;
    protected $template_builder;
    protected $terminal;

// ------------------
// Dependencies init
// ------------------

    protected function __construct()
    {
        $this
            ->setRegistry(new ConfigurationRegistry)
            ->setResponse(new Response)
            ->setRequest(new Request)
            ->setLocator(new Locator)
            ->setTerminal(new Command);
    }

// ------------------
// Dependencies getters/setters
// ------------------

    /**
     * @access private
     */
    private function setTerminal(Command $terminal)
    {
        $this->terminal = $terminal;
        return $this;
    }

    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * @access private
     */
    private function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @access private
     */
    private function setLocator(Locator $locator)
    {
        $this->locator = $locator;
        return $this;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * @access private
     */
    private function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @access private
     */
    private function setRegistry(ConfigurationRegistry $registry)
    {
        $this->registry = $registry;
        return $this;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    public function setController(AbstractController $ctrl)
    {
        $this->controller = $ctrl;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setTemplateBuilder(TemplateBuilder $builder)
    {
        $this->template_builder = $builder;
        return $this;
    }
    
    public function getTemplateBuilder()
    {
        return $this->template_builder;
    }

// ------------------
// Abstracts
// ------------------

    abstract public function distribute();

    abstract public function setInputFile($path);
    abstract public function getInputFile();
    abstract public function setInputPath($path);
    abstract public function getInputPath();
    abstract public function setQueryString($uri);
    abstract public function getQueryString();
    abstract public function setAction($action);
    abstract public function getAction();
    abstract public function setMarkdownParser(MarkdownExtended $parser);
    abstract public function getMarkdownParser();

}

// Endfile
