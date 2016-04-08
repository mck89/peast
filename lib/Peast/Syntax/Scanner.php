<?php
namespace Peast\Syntax;

class Scanner
{
    protected $column = 0;
    
    protected $line = 1;
    
    protected $index = 0;
    
    protected $length;
    
    protected $tokens = array();
    
    function __construct($source, $encoding = null)
    {
        if ($encoding && !preg_match("/UTF-8/i", $encoding)) {
            $source = mb_convert_encoding($source, "UTF-8", $encoding);
        }
        
        $tokenizer =
            "(" .
                "\p{Zs}|\s" . //Whitespaces
            ")" .
            "|" .
            "(" .
                    "[()[\]{}]" . //Brackets
                "|" .
                    "[+\-*/%|&<>^=!]?=" .  // +=, -=, *= ...
                "|" .
                    "/\*" . // /*
                "|" .
                    "\*/" . // */
                "|" .
                    "\|\|" . // ||
                "|" .
                    "&&" .  // &&
                "|" .
                    "\+\+" . // ++
                "|" .
                    "--" . // --
                "|" .
                    "[=!]==" . // ==, ===, !=, !==
                "|" .
                    "(?:<<|>>>?)=?" . // >>, >>>, >>=, >>>=, <<, <<=
                "|" .
                    "\.(?:\.\.)?" . // ., ...
                "|" .
                    "[;\"':~+\-*/%|&<>^!,?\\\\]" . // Single character symbols
                "|" .
                    "[^,?;\.:\"'|&=+\-*/%<>^!~\p{Zs}\s(){}[\]\\\\]+" . // Other
            ")";
        
        preg_match_all("#$tokenizer#u", $source, $this->tokens, PREG_SET_ORDER);
        
        $this->length = count($this->tokens);
    }
    
    public function getColumn()
    {
        return $this->column;
    }
    
    public function getLine()
    {
        return $this->line;
    }
    
    public function getIndex()
    {
        return $this->index;
    }
    
    public function getPosition()
    {
        return new Position(
            $this->getLine(),
            $this->getColumn(),
            $this->getIndex()
        );
    }
    
    public function setPosition(Position $position)
    {
        $this->line = $position->getLine();
        $this->column = $position->getColumn();
        $this->index = $position->getIndex();
        return $this;
    }
    
    protected function getToken()
    {
        if ($this->index < $this->length) {
            $token = $this->token[$this->index];
            $this->index++;
            $ws = false;
            $source = $token[0];
            if (!isset($token[2])) {
                $ws = true;
                $source = preg_split("#\n|\r\n?|\x{2028}|\x{2029}#u", $source);
            }
            return array(
                "source" => $source,
                "whitespace" => $ws
            );
        }
        return null;
    }
    
    protected function consumeToken($token)
    {
        if ($token["whitespace"]) {
            $lines = count($token["source"]) - 1;
            $this->line += $lines;
            $this->column += strlen($token["source"][$lines]);
        } else {
            $this->column += strlen($token["source"]);
        }
    }
    
    protected function unconsumeToken()
    {
        $this->index--;
    }
    
    public function consumeWhitespacesAndComments($lineTerminator = true)
    {
        if (!$lineTerminator) {
            $position = $this->getPosition();
        }
        $comment = $processed = 0;
        while ($token = $this->getToken()) {
            $processed++;
            $source = $token["source"];
            if ($token["whitespace"]) {
                if (count($source) > 1) {
                    if (!$lineTerminator) {
                        $this->setPosition($position);
                        return false;
                    } elseif ($comment === 1) {
                        $comment = 0;
                    }
                }
                $this->consumeToken($token);
            } elseif (!$comment && $source === "//") {
                $comment = 1;
                $this->consumeToken($token);
            }elseif (!$comment && $source === "/*") {
                $comment = 2;
                $this->consumeToken($token);
            } elseif ($comment === 2 && $source === "*/") {
                $comment = 0;
                $this->consumeToken($token);
            } else {
                $this->unconsumeToken();
                return $processed > 1;
            }
        }
        return false;
    }
    
    public function consume($string)
    {
        $this->consumeWhitespacesAndComments();
        
        $token = $this->getToken();
        if (!$token || $token["source"] !== $string) {
            $this->unconsumeToken();
            return false;
        }
        
        $this->consumeToken($token);
        
        return true;
    }
    
    public function consumeArray($sequence)
    {
        $position = $this->getPosition();
        foreach ($sequence as $string) {
            if ($this->consume($string) === false) {
                $this->setPosition($position);
                return false;
            }
        }
        return true;
    }
    
    public function notBefore($tests)
    {
        $position = $this->getPosition();
        foreach ($tests as $test) {
            $testFn = is_array($test) ? "consumeArray" : "consume";
            if ($this->$testFn($test)) {
                $this->setPosition($position);
                return false;
            }
        }
        return true;
    }
    
    public function conumeOneOf($tests)
    {
        foreach ($tests as $test) {
            if ($this->scanner->consume($test)) {
                return $test;
            }
        }
        return null;
    }
}