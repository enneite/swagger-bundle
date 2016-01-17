<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 09:09
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Tests\Creator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BundleGeneratorTest extends WebTestCase
{
    protected $bundleGenerator;
    protected $bundleName;
    protected $bundleNamespace;
    protected $bundleFullName;

    protected function setUp()
    {
        static::bootKernel();
        $this->bundleGenerator = static::$kernel->getContainer()->get('enneite_swagger.bundle_generator');
        $this->bundleName = 'testBundleName';
        $this->bundleNamespace = 'testNamespace';
        $this->bundleFullName = 'testNamespacetestBundleName';
    }

    private function bundleExists($bundleName)
    {
        return array_key_exists(
            $bundleName,
            static::$kernel->getBundles()
        );
    }

    public function testDeleteAndGenerateBundle()
    {
        //check if test bundle exist
        $this->assertEquals(true, $this->bundleExists($this->bundleFullName));
        //remove test bundle
        $this->assertEquals(1, $this->bundleGenerator->deleteBundle(__DIR__.'/../../src/'.$this->bundleNamespace.'/'.$this->bundleName.'/', $this->bundleNamespace, $this->bundleName));
        static::$kernel->removeRegisterBundle($this->bundleFullName);
        //check if test bundl is remove
        $this->assertEquals(false, $this->bundleExists($this->bundleFullName));
        //generate test bundle
        $this->assertEquals(1, $this->bundleGenerator->generate(__DIR__.'/../../src/'.$this->bundleNamespace.'/'.$this->bundleName.'/', $this->bundleNamespace, $this->bundleName));
        static::$kernel->registerTestBundle();
        //check if test bunde is generate
        $this->assertEquals(true, $this->bundleExists($this->bundleFullName));
        //check generate with bundle is already generated
        $this->assertEquals(2, $this->bundleGenerator->generate(__DIR__.'/../../src/'.$this->bundleNamespace.'/'.$this->bundleName.'/', $this->bundleNamespace, $this->bundleName));
    }

    public function testDeleteBundleWithNoFile()
    {
        //check if bundle not exist
        $this->assertEquals(false, $this->bundleExists('bundleNotExist'));
        //remove test bundle with not file
        $this->assertEquals(false, $this->bundleGenerator->deleteBundle(__DIR__.'/not/exist/file/', 'notExistNameSpace', 'notExistBundleName'));
    }

    public function testDeleteBundleWithNotInKernel()
    {
        $bundleNamespace = 'notInKernelNamespace';
        $bundleName = 'notInKernelBundleName';
        $bundlePath = __DIR__.'/../../src/'.$bundleNamespace.'/'.$bundleName.'/';
        //check if bundle not exist
        $this->assertEquals(false, $this->bundleExists('bundleNotExist'));
        //remove test bundle with not file
        $this->assertEquals(2, $this->bundleGenerator->deleteBundle($bundlePath, $bundleNamespace, $bundleName));
        //create new file
        $this->assertEquals(true, mkdir($bundlePath, 777, true));
        file_put_contents($bundlePath.'/'.$bundleNamespace.$bundleName.'.php', '');
    }
}
