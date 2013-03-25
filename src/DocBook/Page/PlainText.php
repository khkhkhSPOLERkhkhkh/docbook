<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Page;

use DocBook\FrontController,
    DocBook\Abstracts\AbstractPage;

/**
 */
class PlainText extends AbstractPage
{

    public static $template_name = 'layout_empty_txt';

    public function parse()
    {
        $docbook = FrontController::getInstance();
        return $docbook->getResponse()->flush(file_get_contents($this->getPath()));
    }

}

// Endfile
