<?php
    
    /**
     * Creates an array of page numbers and links for you to easily manage pagination
     */
    class Pagination
    {
        private $total;
        private $perpage;
        private $current;
        private $linkparts;
        
        /**
         * Initialize the pagination object
         * \param total The total number of items in your list/inventory/whatever
         * \param perpage The number of items per page
         * \param current The current page number (starts at 1, not 0). If the page number
         *                Is not a valid page number then page 1 is used instead.
         * \param linktemplate This is a string representing the links for pages. You can
         *                     use the format {page} and it will be replaced by the page number
         *                     for example "/members/{page}/" will link to /members/1/, /members/2/ etc
         */
        public function __construct($total, $perpage, $current, $linktemplate)
        {
            $this->total = $total;
            $this->perpage = $perpage;
            if($this->IsValid((int)$current))
                $this->current = (int)$current;
            else
                $this->current = 1;
            $this->linkparts = preg_split('/{page}/', $linktemplate);
        }
        
        /**
         * Tells you what LIMITs to use in an SQL query for the current page
         * \return An array with 2 items, the first is the start of the limit and the second the end
         *         For example the function returns array($start, $end) which you can use in an SQL query
         *         for "SELECT * FROM `test` LIMIT ?, ?"
         */
        public function GetLimits()
        {
            return array(($this->current - 1) * $this->perpage, $this->perpage);
        }

        /**
         * Returns an array of all pages
         * \return An array with all pages is returned, each page is an array that has 3
         *         items in it, page, link and active.
         *         Or in php terms its an array of array('page' => $page, 'link' => $link, 'active' => $active)
         *         where page is the page number, link is the link to that page and active is true for the current
         *         page only. You can bind this list directly to your view and loop over it to render your pagination.
         */
        public function GetList()
        {
            $list = array();
            // Double loop, pages increment by one, i increments by items_per_page
            for($i = 0, $page = 1; $i < $this->total; $i += $this->perpage, $page++)
            {
                $list[] = array('page' => $page,
                                'link' => $this->MakeLink($page),
                                'active' => ($this->current == $page ? true : false));

            }
            return $list;
        }
        
        private function MakeLink($page)
        {
            $link = '';
            for($i = 0; $i < count($this->linkparts) - 1; $i++)
            {
                $link .= $this->linkparts[$i] . $page;
            }
            $link .= $this->linkparts[$i];
            return $link;
        }
        
        /**
         * Returns the current page number
         * \return Integer value for the current page number
         */
        public function GetCurrent()
        {
            return $this->current;
        }
        
        /**
         * Verify if a certain page numer (presumably requested by the user) is valid
         * \param pagenum integer for the page number you want to verify
         * \return Returns true when the page is valid, false otherwise.
         */
        public function IsValid($pagenum)
        {
            if($pagenum <= 0)
                return false;
            
            if((($pagenum - 1) * $this->perpage) <= $this->total)
                return true;
            else
                return false;
        }
    }

?>
