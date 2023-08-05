<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
class FileUploader
{

    private $projectDir;
    private $slugger;

    public function __construct(ParameterBagInterface $parameterBag, SluggerInterface $slugger)
    {
        $this->projectDir = $parameterBag->get('kernel.project_dir');
        $this->slugger = $slugger;
    }
            

    public function getFilePath()
    {
        $filePath = $this->projectDir . '/public/products';
        return $filePath;
    }


    public function upload(UploadedFile $file, string $fileName): string
    {
        $safeFilename = $this->slugger->slug($fileName);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getFilePath(), $fileName);
        } catch (FileException $e) {
            return null;
        }

        return $fileName;
    }
}