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

        public function Render($contents)
        {
            $numlines = preg_match_all('/\n/m', $contents, $derp);
            if(!preg_match('/\n$/m', $contents))
                $numlines++;

            $output = '<div style="text-align: right; float: left; margin-right: 10px;">';

            for($i = 1; $i <= $numlines; $i++)
                $output .= $i . '.<br />';
            
            $output .= '</div><div>' . nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($contents))) . '</div>';
            return $output;
        }
    }
?>
