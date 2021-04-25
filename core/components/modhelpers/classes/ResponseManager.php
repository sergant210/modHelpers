<?php
namespace modHelpers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ResponseManager
{
    /** @var \modX $modx */
    protected $modx;
    /** @var SymfonyResponse $response */
    protected $response;
    /** @var bool $isSent True if the response is already sent */
    protected $isSent = false;
    /** @var bool */
    protected $isPrepared = false;

    protected $charsArray = [
        '0'    => ['°', '₀', '۰'],
        '1'    => ['¹', '₁', '۱'],
        '2'    => ['²', '₂', '۲'],
        '3'    => ['³', '₃', '۳'],
        '4'    => ['⁴', '₄', '۴', '٤'],
        '5'    => ['⁵', '₅', '۵', '٥'],
        '6'    => ['⁶', '₆', '۶', '٦'],
        '7'    => ['⁷', '₇', '۷'],
        '8'    => ['⁸', '₈', '۸'],
        '9'    => ['⁹', '₉', '۹'],
        'a'    => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'],
        'b'    => ['б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'],
        'c'    => ['ç', 'ć', 'č', 'ĉ', 'ċ'],
        'd'    => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'],
        'e'    => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'],
        'f'    => ['ф', 'φ', 'ف', 'ƒ', 'ფ'],
        'g'    => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'],
        'h'    => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'],
        'i'    => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'],
        'j'    => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج'],
        'k'    => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'],
        'l'    => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'],
        'm'    => ['м', 'μ', 'م', 'မ', 'მ'],
        'n'    => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'],
        'o'    => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'],
        'p'    => ['п', 'π', 'ပ', 'პ', 'پ'],
        'q'    => ['ყ'],
        'r'    => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'],
        's'    => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'],
        't'    => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'],
        'u'    => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'],
        'v'    => ['в', 'ვ', 'ϐ'],
        'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ'],
        'x'    => ['χ', 'ξ'],
        'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'],
        'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'],
        'aa'   => ['ع', 'आ', 'آ'],
        'ae'   => ['ä', 'æ', 'ǽ'],
        'ai'   => ['ऐ'],
        'at'   => ['@'],
        'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
        'dj'   => ['ђ', 'đ'],
        'dz'   => ['џ', 'ძ'],
        'ei'   => ['ऍ'],
        'gh'   => ['غ', 'ღ'],
        'ii'   => ['ई'],
        'ij'   => ['ĳ'],
        'kh'   => ['х', 'خ', 'ხ'],
        'lj'   => ['љ'],
        'nj'   => ['њ'],
        'oe'   => ['ö', 'œ', 'ؤ'],
        'oi'   => ['ऑ'],
        'oii'  => ['ऒ'],
        'ps'   => ['ψ'],
        'sh'   => ['ш', 'შ', 'ش'],
        'shch' => ['щ'],
        'ss'   => ['ß'],
        'sx'   => ['ŝ'],
        'th'   => ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
        'ts'   => ['ц', 'ც', 'წ'],
        'ue'   => ['ü'],
        'uu'   => ['ऊ'],
        'ya'   => ['я'],
        'yu'   => ['ю'],
        'zh'   => ['ж', 'ჟ', 'ژ'],
        '(c)'  => ['©'],
        'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'],
        'B'    => ['Б', 'Β', 'ब'],
        'C'    => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'],
        'D'    => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'],
        'E'    => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'],
        'F'    => ['Ф', 'Φ'],
        'G'    => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'],
        'H'    => ['Η', 'Ή', 'Ħ'],
        'I'    => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'],
        'K'    => ['К', 'Κ'],
        'L'    => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'],
        'M'    => ['М', 'Μ'],
        'N'    => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'],
        'O'    => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'],
        'P'    => ['П', 'Π'],
        'R'    => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'],
        'S'    => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'],
        'T'    => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'],
        'U'    => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'],
        'V'    => ['В'],
        'W'    => ['Ω', 'Ώ', 'Ŵ'],
        'X'    => ['Χ', 'Ξ'],
        'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'],
        'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ'],
        'AE'   => ['Ä', 'Æ', 'Ǽ'],
        'CH'   => ['Ч'],
        'DJ'   => ['Ђ'],
        'DZ'   => ['Џ'],
        'GX'   => ['Ĝ'],
        'HX'   => ['Ĥ'],
        'IJ'   => ['Ĳ'],
        'JX'   => ['Ĵ'],
        'KH'   => ['Х'],
        'LJ'   => ['Љ'],
        'NJ'   => ['Њ'],
        'OE'   => ['Ö', 'Œ'],
        'PS'   => ['Ψ'],
        'SH'   => ['Ш'],
        'SHCH' => ['Щ'],
        'SS'   => ['ẞ'],
        'TH'   => ['Þ'],
        'TS'   => ['Ц'],
        'UE'   => ['Ü'],
        'YA'   => ['Я'],
        'YU'   => ['Ю'],
        'ZH'   => ['Ж'],
        ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
    ];

    /**
     * @var \modX $modx
     */
    public function __construct($modx)
    {
        $this->modx = $modx;
        session_register_shutdown();
        register_shutdown_function([$this,"send"]);
    }
    /**
     * @return SymfonyResponse
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Prepares the Response before it is sent to the client.
     * @param Request $request
     * @return ResponseManager
     */
    public function prepare(Request $request)
    {
        $this->response->prepare($request);
        $this->isPrepared = true;
        return $this;
    }
    /**
     * Sends HTTP headers and content and terminate the current script.
     * @param bool $needPrepare
     */
    public function send($needPrepare = true)
    {
        if ($this->response instanceof SymfonyResponse && !$this->isSent) {
            if ($needPrepare && !$this->isPrepared) {
                $this->prepare(request());
            }
            $this->response->send();
        }
        $this->isSent = true;
        exit;
    }
    /**
     * Return a new response from the application.
     *
     * @param  string  $content
     * @param  int  $status
     * @param  array  $headers
     * @return ResponseManager
     */
    public function make($content = '', $status = 200, array $headers = [])
    {
        $this->response = new Response($content, $status, $headers);
        return $this;
    }

    /**
     * Return a new response from the application.
     *
     * @param  string  $chunk
     * @param  array  $data
     * @param  int  $status
     * @param  array  $headers
     * @return ResponseManager
     */
    public function chunk($chunk, $data = [], $status = 200, array $headers = [])
    {
        if (is_string($chunk)) {
            $content = chunk($chunk);
        }
        $this->response = $this->make($content ?: '', $status, $headers);
        $this->response->data = $data;
        return $this;
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return ResponseManager
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        $this->response = new JsonResponse($data, $status, $headers, $options);
        return $this;
    }

    /**
     * Return a new JSONP response from the application.
     *
     * @param  string  $callback
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return ResponseManager
     */
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return $this->json($data, $status, $headers, $options)->setCallback($callback);
    }

    /**
     * Create a new file download response.
     *
     * @param  \SplFileInfo|string  $file
     * @param  string  $name
     * @param  array  $headers
     * @param  string|null  $disposition
     * @return ResponseManager
     */
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        try {
            $this->response = new BinaryFileResponse($file, 200, $headers, true, $disposition);
        }
        catch (FileException $e) {
            log_error($e->getMessage());
            $this->response = new Response('', 200, $headers);
            return $this;
        }

        if (! is_null($name)) {
            return $this->response->setContentDisposition($disposition, $name, str_replace('%', '', $this->transliterate($name)));
        }
        return $this;
    }

    /**
     * Return the raw contents of a binary file.
     *
     * @param  \SplFileInfo|string  $file
     * @param  array  $headers
     * @return ResponseManager
     */
    public function file($file, array $headers = [])
    {
        try {
            $this->response = new BinaryFileResponse($file, 200, $headers);
        }
        catch (FileException $e) {
            log_error($e->getMessage());
            $this->response = new Response('', 200, $headers);
        }

        return $this;
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string  $value
     * @return string
     */
    protected function transliterate($value)
    {
        foreach ($this->charsArray as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }
    /**
     * If this is set to true, the file will be unlinked after the request is send
     * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
     *
     * @return ResponseManager
     */
    public function deleteFile()
    {
        if ($this->response instanceof BinaryFileResponse) {
            $this->response->deleteFileAfterSend(true);
        }

        return $this;
    }

    /**
     * Handle dynamic calls.
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->response, $method)) {
            call_user_func_array(array($this->response, $method), $parameters);
        }
        return $this;
    }
}