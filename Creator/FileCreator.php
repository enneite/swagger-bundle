<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 25/06/15
 * Time: 08:21
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Creator;

class FileCreator
{

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    public function __construct($fs)
    {
        $this->fs = $fs;
    }

    /**
     * create file :.
     *
     * @param $path
     * @param $content
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createFile($path, $content)
    {
        $res = @file_put_contents($path, $content);
        if (false === $res) {
            throw new\Exception("can not put content in $path");
        }

        return true;
    }

    /**
     * create directory.
     *
     * @param $path
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function createDirectory($path)
    {
        if (!$this->exists(realpath($path))) {
            $res = $this->fs->mkdir($path);
        };

        return true;
    }

    /**
     * look if the file exist.
     *
     *
     * @param $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return $this->fs->exists($path);
    }

    /**
     * get file content.
     *
     * @param $file
     *
     * @throws \Exception
     *
     * @return string
     */
    public function get($file)
    {
        $res = @file_get_contents($file);
        if (false === $res) {
            throw new\Exception("can not get content from $file");
        }

        return $res;
    }

    public function realpath($path)
    {
        return realpath($path);
    }
}
