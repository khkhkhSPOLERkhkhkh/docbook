<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package     DocBook
 * @license     GPL-v3
 * @link        https://github.com/atelierspierrot/docbook
 */

namespace DocBook\Controller;

use DocBook\FrontController,
    DocBook\Helper,
    DocBook\Locator,
    DocBook\Abstracts\AbstractController,
    DocBook\WebFilesystem\DocBookFile,
    DocBook\WebFilesystem\DocBookRecursiveDirectoryIterator;

use Markdown\Parser,
    Markdown\ExtraParser;

use WebFilesystem\WebFilesystem,
    WebFilesystem\WebFileInfo;

use Symfony\Component\Finder\Finder;

/**
 */
class DefaultController extends AbstractController
{

    public function indexAction($path)
    {
        if (@is_dir($path)) {
            return $this->directoryAction($path);
        } else {
            return $this->fileAction($path);
        }
    }

    public function fileAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getpath());
        $tpl_params = array(
            'page' => $dbfile->getDocBookStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
        );


        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = 'Home';
            }
        }

        $file_content = file_get_contents($this->getPath());
        $md_parser = $this->docbook->getMarkdownParser();
        $content = $this->docbook->display(
            $md_parser->transform($file_content),
            'content',
            array('page'=>$dbfile->getDocBookStack())
        );

        return array('default', $content, $tpl_params);
    }

    public function directoryAction($path)
    {
        $this->setPath($path);
        $dbfile = new DocBookFile($this->getpath());
        $readme_content = $dir_content = '';

        $index = $dbfile->findIndex();
        if (file_exists($index)) {
            return $this->fileAction($index);
        }

        $readme = $dbfile->findReadme();
        if (file_exists($readme)) {
            $this->docbook->setInputFile($readme);
            $readme_dbfile = new DocBookFile($readme);
            $file_content = file_get_contents($readme);
            $md_parser = $this->docbook->getMarkdownParser();
            $readme_content = $this->docbook->display(
                $md_parser->transform($file_content),
                'content',
                array('page'=>$readme_dbfile->getDocBookStack())
            );
        }

        $tpl_params = array(
            'page' => $dbfile->getDocBookStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
        );

        $tpl_params['title'] = Helper::buildPageTitle($this->getPath());
        if (empty($tpl_params['title'])) {
            if (!empty($tpl_params['breadcrumbs'])) {
                $tpl_params['title'] = Helper::buildPageTitle(end($tpl_params['breadcrumbs']));
            } else {
                $tpl_params['title'] = 'Home';
            }
        }
        $dir_content = $this->docbook->display($dbfile->getDocBookScanStack(), 'dirindex');

        return array('default', $dir_content.$readme_content, $tpl_params);
    }

    public function htmlOnlyAction($path)
    {
        $this->setPath($path);
        $md_parser = $this->docbook->getMarkdownParser();
        $content = file_get_contents($this->getPath());
        return array('layout_empty_html', $md_parser->transform($content));
    }

    public function plainTextAction($path)
    {
        $this->setPath($path);
        $ctt = $this->docbook->getResponse()->flush(file_get_contents($this->getPath()));
        return array('layout_empty_txt', $ctt);
    }

    public function downloadAction($path)
    {
        $this->setPath($path);
        $this->docbook->getResponse()->download($path, 'text/plain');
        exit;
    }

    public function searchAction($path)
    {
        $this->setPath($path);
        $search = $this->docbook->getRequest()->getGet('s');
        if (empty($search)) return $this->indexAction($path);

        $_s = Helper::processDocBookSearch($search, $this->getPath());

        $dbfile = new DocBookFile($this->getpath());
        $tpl_params = array(
            'page' => $dbfile->getDocBookStack(),
            'breadcrumbs' => Helper::getBreadcrumbs($this->getPath()),
            'title' => sprintf('Search for "%s"', $search)
        );

        $search_content = $this->docbook->display($_s, 'search', array(
            'search_str' => $search,
            'path' => Helper::buildPageTitle($this->getPath()),
        ));
        return array('default', $search_content, $tpl_params);
    }

}

// Endfile
