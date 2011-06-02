<?php

    // Supposed to become a universal interface that we can use for every god damn highlighting library on the planet!
    class SourceFormat
    {
        private $lang;
        private $nums;

        public function __construct()
        {
            $this->lang = 0;
            $this->nums = true;
        }

        public function Render($contents, $langid)
        {
            // First escape all html chars
            $contents = htmlspecialchars($contents);

            $lines = explode("\n", $contents);
            $numlines = count($lines);
            
            $output = '<div class="sourceformat"><div class="nums">';
            for ($i = 1; $i <= $numlines; $i++)
                if ($i % 2)
                    $output .= '<div class="odd">' . $i . '.</div>';
                else  
                    $output .= '<div class="even">' . $i . '.</div>';

            $output .= '</div><div class="code">';
            
            for ($i = 0; $i < $numlines; $i++)
            {
                $line = str_replace(array(' ', "\r"), array('&nbsp;', ''), $lines[$i]);
                if(!$line)
                    $line = '&nbsp;';

                if (($i + 1) % 2)
                    $output .= '<div class="odd">' . $line . '</div>';
                else
                    $output .= '<div class="even">' . $line . '</div>';
            }
            
            $output .= '</div></div>';
            return $output;
        }
    }
?>
