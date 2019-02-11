<?php
namespace modHelpers;

use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use modMediaSource;
use modFileMediaSource;

class UploadedFile extends SymfonyUploadedFile
{
    /** @var string The cache copy of the file's hash name. */
    protected $hashName = null;
    /** @var  modMediaSource */
    protected $source;
    /** @var string */
    protected $fileUrl;


    /**
     * Store the uploaded file with the original name.
     *
     * @param  string  $path
     * @param  string|int|modMediaSource  $source
     * @return string|false
     */
    public function storeAsOriginal($path, $source = null)
    {
        return $this->storeAs($path, $this->originalName(), $source);
    }

    /**
     * Store the uploaded file.
     *
     * @param  string  $path
     * @param  string|int|modMediaSource  $source
     * @return string|false
     */
    public function store($path, $source = null)
    {
        return $this->storeAs($path, $this->hashName(), $source);
    }

    /**
     * Store the uploaded file with a given name.
     *
     * @param  string  $path
     * @param  string  $name
     * @param  string|int|modMediaSource  $source
     * @return string|bool
     */
    public function storeAs($path, $name, $source = null)
    {
        $path = rtrim($path, '/') . '/';
        if ($source = $this->getSource($source)) {
            //$this->source->createContainer($path, '/');
            if ($source instanceof modFileMediaSource) {
                return $this->saveUploadedFile($path, $name);
            } else {
                return $source->uploadObjectsToContainer($path, array($this->uploadInfo(['name' => $name])));
            }

        } else {
            return false;
        }
    }
    /**
     * Save an uploaded file at the specified path.
     *
     * @param string $path
     * @param string $name
     * @return boolean|string
     */
    public function saveUploadedFile($path, $name) {
        /** @var modFileMediaSource $source */
        if ( !$source = $this->getSource() ) {
            log_error($source->xpdo->lexicon('source_err_nf'));
            return false;
        }
        $bases = $source->getBases($path);
        $path = $bases['pathAbsolute'] . ltrim($path,'/');
        $name = ltrim($name,'/');
        if ( !$source->checkFiletype($file = $path . $name) ) {
            $source->xpdo->lexicon->load('core:file');
            log_error($source->xpdo->lexicon('upf_err_filetype'));
            return false;
        }
        try {
            $this->move($path, $name);
        } catch (\Exception $e) {
            log_error('Upload file error: ' . $e->getMessage());
            return false;
        }

        $this->fileUrl = str_replace(MODX_BASE_PATH, '', $file);
        $source->xpdo->logManagerAction('file_upload', '', $this->fileUrl);
        return $file;
    }

    /**
     * Retrieve a file data in $_FILES format.
     * @param array $data
     * @return array
     */
    public function uploadInfo($data = [])
    {
        return [
            'name' => $data['name'] ?: $this->getClientOriginalName(),
            'tmp_name' => $this->getPathname(),
            'type' => $this->getClientMimeType(),
            'error' => $this->getError(),
            'size' => $this->getClientSize(),
        ];
    }
    /**
     * Get a MediaSource object
     * @param string|int|modMediaSource $source
     * @return bool|modMediaSource
     */
    public function getSource($source = null) {
        if (!is_null($this->source)) return $this->source;
        if (is_object($source) && $source instanceof modMediaSource) {
            return $this->source = $source;
        }
        $modx = app('modx');
        if (!$source) {
            $source = $modx->getOption('default_media_source', null, 1);
        }
        if (is_numeric($source)) {
            $criteria = array('id' => $source);
        } elseif (is_string($source)) {
            $criteria = array('name' => $source);
        } else {
            $criteria = [
                'id' => $modx->getOption('default_media_source', null, 1)
            ];
        }
        if (!class_exists('modMediaSource')) $modx->loadClass('sources.modMediaSource');
        /** @var \modX $modx */
        $this->source = $modx->getObject('modMediaSource', $criteria);
        if (empty($this->source) || !$this->source->getWorkingContext()) return false;
        $this->source->set('ctx', $modx->context);
        $this->source->initialize();
        return $this->source;
    }

    /**
     * Create a new file instance from a base instance.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @param  bool $test
     * @return static
     */
    public static function createFromBase(SymfonyUploadedFile $file, $test = false)
    {
        return $file instanceof static ? $file : new static(
            $file->getPathname(),
            $file->getClientOriginalName(),
            $file->getClientMimeType(),
            $file->getClientSize(),
            $file->getError(),
            $test
        );
    }

    /**
     * Get the fully qualified path to the file.
     *
     * @return string
     */
    public function path()
    {
        return $this->getRealPath();
    }

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then it should not be considered as a safe value.
     *
     * @return string|null The original name
     */
    public function originalName()
    {
        return $this->getClientOriginalName();
    }
    /**
     * Get the file's extension.
     *
     * @return string
     */
    public function extension()
    {
        return $this->guessExtension();
    }

    /**
     * Get the file's extension supplied by the client.
     *
     * @return string
     */
    public function clientExtension()
    {
        return $this->guessClientExtension();
    }

    /**
     * Get a filename for the file.
     *
     * @param  string  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/') . '/';
        }

        if (empty($this->hashName)) {
            $fh = fopen($this->getPathname(), 'r');
            $this->hashName = sha1(fread($fh, 8192));
            fclose($fh);
        }

        return $path . $this->hashName . '.' . $this->guessExtension();
    }

    /**
     * Get the url of the uploaded file.
     * @return string
     */
    public function getStoredFileUrl()
    {
        return $this->fileUrl;
    }
    /**
     * Get the name of the uploaded file.
     * @return string
     */
    public function getStoredFileName()
    {
        return basename($this->fileUrl);
    }
    /**
     * Get the path of the uploaded file.
     * @return string
     */
    public function getStoredFilePath()
    {
        return request()->root() . '/' . $this->fileUrl;
    }
}