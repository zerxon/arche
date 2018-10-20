<?php
/**
 * 
 * @author: wallace wallaceleung@163.com
 * @date: 14-11-12
 */

class Pagination {
    private $_totalPages;

    public $pageIndex;
    public $pageSize;
    public $totalRecords;
    public $showSize;
    public $url;

    public function __construct($pageModel) {
        $this->pageIndex = $pageModel['pageIndex'];
        $this->pageSize = $pageModel['pageSize'];
        $this->totalRecords = $pageModel['totalRecords'];
        $this->_totalPages = $pageModel['totalPages'];
    }

    public function page() {
        $offset = floor($this->showSize / 2);

        $pagination = array(
            'pageIndex' => $this->pageIndex,
            'pageSize' => $this->pageSize,
            'totalPages' => $this->_totalPages,
            'totalRecords' => $this->totalRecords,
            'showSize' => $this->showSize,
            'url' => $this->url,
            'pageNames' => array()
        );

        if($this->_totalPages <=1 || $this->pageIndex == 1)
            $pagination['isStart'] = true;

        if($this->_totalPages <= 1 || $this->pageIndex == $this->_totalPages)
            $pagination['isEnd'] = true;

        if($this->_totalPages == 1) {
            array_push($pagination['pageNames'], 1);
        }
        else if($this->pageIndex <= $offset) {
            $end = $this->_totalPages > $this->showSize ? $this->showSize : $this->_totalPages;
            for($i = 1; $i <= $end; $i++)
                array_push($pagination['pageNames'], $i);
        }
        else if($this->_totalPages - $this->pageIndex <= $offset) {
            if($this->_totalPages - $this->showSize > 0)
                $start = $this->_totalPages - $this->showSize + 1;
            else
                $start = 1;

            for($i = $start; $i <= $this->_totalPages; $i++)
                array_push($pagination['pageNames'], $i);
        }
        else {
            $start = $this->pageIndex - $offset;
            $end = $this->pageIndex + $offset;

            if($this->showSize % 2 == 0)
                $end = $end - 1;

            for($i = $start; $i <= $end; $i++)
                array_push($pagination['pageNames'], $i);
        }

        return $pagination;
    }
}