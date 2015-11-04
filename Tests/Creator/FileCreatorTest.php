<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 09:09
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Creator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Enneite\SwaggerBundle\Creator\FileCreator;

class FileCreatorTest extends WebTestCase
{
    /**
     * @expectedException \Exception
     */
    public function testFileCreateWithException()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $creator = new FileCreator($fs);

        $creator->createFile('/dont/have/this/path/in/your/machine/please', 'un contenu');
    }

    public function testFileCreaten()
    {
        $fs = new Filesystem();
        $creator = new FileCreator($fs);

        try {
            $res = $creator->createFile(__DIR__.'/file.txt', 'delete this file please');
            $this->assertTrue($res);
            $fs->remove(__DIR__.'/file.txt');
        } catch (\Exception $e) {
            //
        }
    }

    public function testDirectoryCreate()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $fs->expects($this->any())
            ->method('mkdir')
            ->will($this->returnValue(true));
        $fs->expects($this->any())
        ->method('exists')
        ->will($this->returnValue(false));
        $creator = new FileCreator($fs);

        $res = $creator->createDirectory('/this/is/a/directory');
        $this->assertTrue($res);
    }

    /**
     * @expectedException \Exception
     */
    public function testDirectoryWithException()
    {
        $e = $this->getMockBuilder('\Exception')->disableOriginalConstructor()->getMock();
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $fs->expects($this->any())
            ->method('mkdir')
            ->will($this->throwException($e));
        $fs->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));
        $creator = new FileCreator($fs);

        $res = $creator->createDirectory('/this/is/a/directory');
    }

    public function testDirectoryAllReadyExists()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $fs->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $creator = new FileCreator($fs);

        $res = $creator->createDirectory('/this/is/a/directory');
        $this->assertTrue($res);
    }

    public function testExists()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $fs->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        $creator = new FileCreator($fs);

        $res = $creator->createDirectory('/this/is/a/directory');
        $this->assertTrue($res);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithException()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $creator = new FileCreator($fs);

        $res = $creator->get('/this/is/a/file');
        var_dump($res);
    }

    public function testGet()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $creator = new FileCreator($fs);

        $res = $creator->get(realpath(__DIR__.'/../../Resources/example/config/swagger.yaml'));
        $this->assertInternalType('string', $res);
        $this->assertTrue(strlen($res) > 0);
    }

    public function testRealpath()
    {
        $fs = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $creator = new FileCreator($fs);

        $res = $creator->realpath(__DIR__.'/../../Resources/example/config/swagger.yaml');
        $this->assertInternalType('string', $res);
        $this->asserttrue(strpos($res, '/Resources/example/config/swagger.yaml') !== 0);

        $res = $creator->realpath('/this/is/not/a/real/file');
        $this->assertTrue(false == $res);
    }
}
