<?php
namespace modHelpers;

use ArrayObject;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    use ResponseTrait;

    /** @var array $data Data for the chunk */
    public $data = [];

    /**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return SymfonyResponse
     */
    public function setContent($content)
    {
        $this->original = $content;

        if ($this->shouldBeJson($content)) {
            $this->header('Content-Type', 'application/json');

            $content = $this->ToJson($content);
        }

        return parent::setContent($content);
    }
    /**
     * Set data for the chunk.
     *
     * @param  array  $data
     * @return $this
     */
    public function with(array $data = [])
    {
        $this->data = $data;
        return $this;
    }
    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $this->isJsonable($content) ||
            $content instanceof ArrayObject ||
            $content instanceof JsonSerializable ||
            is_array($content);
    }

    /**
     * Convert the given content into JSON format.
     *
     * @param  mixed   $content
     * @return string
     */
    protected function ToJson($content)
    {
        if ($this->isJsonable($content)) {
            return $content->toJson();
        }

        return json_encode($content);
    }
    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString()
    {
        $this->processData();

        return parent::__toString();
    }
    /**
     * Sends HTTP headers and content.
     *
     * @return SymfonyResponse
     */
    public function send()
    {
        $this->processData();

        return parent::send();
    }

    protected function processData()
    {
        if (!empty($this->data)) {
            $this->setContent(parse($this->getContent(), $this->data));
        }
    }
}