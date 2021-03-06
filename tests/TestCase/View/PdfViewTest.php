<?php

namespace CakePdf\Test\TestCase\View;

use CakePdf\Pdf\CakePdf;
use CakePdf\Pdf\Engine\AbstractPdfEngine;
use CakePdf\View\PdfView;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * Dummy engine
 */
class PdfTestEngine extends AbstractPdfEngine
{

    public function output()
    {
        return $this->_Pdf->html();
    }
}

/**
 * Dummy controller
 */
class PdfTestPostsController extends Controller
{

    public $name = 'Posts';

    public $pdfConfig = ['engine' => 'PdfTest'];
}

/**
 * PdfViewTest class
 */
class PdfViewTest extends TestCase
{

    /**
     * setup callback
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Configure::write('CakePdf', [
            'engine' => '\\' . __NAMESPACE__ . '\PdfTestEngine',
        ]);

        $request = new ServerRequest();
        $response = new Response();
        $this->View = new PdfView($request, $response);
        $this->View->layoutPath('pdf');
    }

    /**
     * testRender
     *
     */
    public function testConstruct()
    {
        $result = $this->View->response->type();
        $this->assertEquals('application/pdf', $result);

        $result = $this->View->pdfConfig;
        $this->assertEquals(['engine' => '\\' . __NAMESPACE__ . '\PdfTestEngine'], $result);

        $result = $this->View->renderer();
        $this->assertInstanceOf(CakePdf::class, $result);
    }

    /**
     * testRender
     *
     */
    public function testRender()
    {
        $this->View->templatePath('Posts');
        $this->View->set('post', 'This is the post');
        $result = $this->View->render('view', 'default');

        $this->assertContains('<h2>Rendered with default layout</h2>', $result);
        $this->assertContains('Post data: This is the post', $result);
    }

    /**
     * Test rendering a template that does not generate any output
     *
     */
    public function testRenderTemplateWithNoOutput()
    {
        $this->View->templatePath('Posts');
        $result = $this->View->render('empty', 'empty');
        $this->assertSame('', $result);
    }

    /**
     * Test rendering an Error template, which should  default to standard layout
     *
     */
    public function testRenderErrorTemplate()
    {
        $request = new ServerRequest();
        $response = new Response();
        $this->View = new PdfView($request, $response, null, [ 'templatePath' => 'Error' ]);

        $this->assertNull($this->View->subDir);
        $this->assertNull($this->View->layoutPath);

        $result = $this->View->response->type();
        $this->assertEquals('text/html', $result);
    }
}
