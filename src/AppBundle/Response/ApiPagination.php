<?php
namespace AppBundle\Response;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class ApiPagination
{
    /**
     * @var int
     * @Serializer\Accessor(getter="getPageCount",setter="setPageCount")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("pageCount")
     */
    private $pageCount = 1;
    /**
     * @var int|null
     * @Serializer\Accessor(getter="getRecordCount",setter="setRecordCount")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("recordCount")
     */
    private $recordCount = 0;
    /**
     * @var int|null
     * @Serializer\Accessor(getter="getItemsPerPage",setter="setItemsPerPage")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("itemsPerPage")
     */
    private $itemsPerPage = 20;
    /**
     * @var null|string
     * @Serializer\Accessor(getter="getFirstLink",setter="setFirstLink")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("firstLink")
     */
    private $firstLink = '';
    /**
     * @var null|string
     * @Serializer\Accessor(getter="getLastLink",setter="setLastLink")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("lastLink")
     */
    private $lastLink = '';
    /**
     * @var null|string
     * @Serializer\Accessor(getter="getPrevLink",setter="setPrevLink")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("prevLink")
     */
    private $prevLink = '';
    /**
     * @var null|string
     * @Serializer\Accessor(getter="getNextLink",setter="setNextLink")
     * @Serializer\Type("string")
     * @Serializer\SerializedName("nextLink")
     */
    private $nextLink = '';
    /**
     * @var int|null
     * @Serializer\Accessor(getter="getCurrentPage",setter="setCurrentPage")
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("currentPage")
     */
    private $currentPage = 1;
    /**
     * @var bool|null
     * @Serializer\Accessor(getter="getCircular",setter="setCircular")
     * @Serializer\Type("boolean")
     */
    private $circular = false;
    /**
     * Pagination constructor.
     * @param int|null $itemsPerPage
     * @param int|null $recordCount
     * @param int|null $offset
     * @param bool|null $circular
     */
    public function __construct(int $itemsPerPage = null, int $recordCount = null, int $offset = 0, bool $circular = false)
    {
        $this->itemsPerPage = (is_null($itemsPerPage) || $itemsPerPage == 0) ? 1 : $itemsPerPage;
        $this->recordCount = $recordCount ?? 0;
        $pageCalc = (int) ceil($this->recordCount / $this->itemsPerPage);
        $this->pageCount = $pageCalc <= 0 ? 1 : $pageCalc;
        $this->currentPage = $this->calculatePage($offset);
        $this->circular = $circular;
        $this->setFirstLink()->setLastLink()->setPrevLink()->setNextLink();
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * @param $pageCount
     * @return $this
     */
    public function setPageCount($pageCount)
    {
        $this->pageCount = $pageCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getRecordCount()
    {
        return $this->recordCount;
    }

    /**
     * @param $recordCount
     * @return $this
     */
    public function setRecordCount($recordCount)
    {
        $this->recordCount = $recordCount;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstLink()
    {
        return $this->firstLink;
    }

    /**
     * @param string|null $firstLink
     * @return $this
     */
    public function setFirstLink(string $firstLink = null)
    {
        if(!is_null($firstLink)){
            $this->firstLink = $firstLink;
            return $this;
        }
        if($this->recordCount < $this->itemsPerPage ){
            $this->firstLink = null;
            return $this;
        }
        $this->firstLink = '?offset=0&limit='.$this->itemsPerPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastLink()
    {
        return $this->lastLink;
    }

    /**
     * @param string|null $lastLink
     * @return $this
     */
    public function setLastLink(string $lastLink = null)
    {
        if(!is_null($lastLink)){
            $this->lastLink = $lastLink;
            return $this;
        }
        if($this->recordCount < $this->itemsPerPage ){
            $this->lastLink = null;
            return $this;
        }
        $offset = ($this->pageCount - 1) * $this->itemsPerPage;
        $this->lastLink = '?offset='.$offset.'&limit='.$this->itemsPerPage;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrevLink()
    {
        return $this->prevLink;
    }

    /**
     * @param string|null $prevLink
     * @return $this
     */
    public function setPrevLink(string $prevLink = null)
    {
        if(!is_null($prevLink)){
            $this->prevLink = $prevLink;
            return $this;
        }
        if($this->currentPage == 1){
            $this->prevLink = '';
            if($this->circular){
                $offset = ($this->pageCount - 1) * $this->itemsPerPage;
                $this->prevLink = '?offset='.$offset.'&limit='.$this->itemsPerPage;
            }
        }
        else{
            if(($this->currentPage) - 2 <= 1){
                $this->prevLink = null;
            }
            else{
                $offset = ($this->currentPage - 2) * $this->itemsPerPage;
                $this->prevLink = '?offset='.$offset.'&limit='.$this->itemsPerPage;
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getNextLink()
    {
        return $this->nextLink;
    }

    /**
     * @param string|null $nextLink
     * @return $this
     */
    public function setNextLink(string $nextLink = null)
    {
        if(!is_null($nextLink)){
            $this->nextLink = $nextLink;
            return $this;
        }
        if($this->currentPage == $this->pageCount){
            $this->nextLink = '';
            if($this->circular){
                $this->nextLink = '?offset=0&limit='.$this->itemsPerPage;
            }
        }
        else{
            if($this->currentPage == $this->pageCount){
                $this->nextLink = null;
            }
            else{
                $offset = ($this->currentPage) * $this->itemsPerPage;
                $this->nextLink = '?offset='.$offset.'&limit='.$this->itemsPerPage;
            }
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage(int $currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $itemsPerPage
     * @return $this
     */
    public function setItemsPerPage(int $itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCircular()
    {
        return $this->circular;
    }

    /**
     * @param bool $circular
     * @return $this
     */
    public function setCircular(bool $circular)
    {
        $this->circular = $circular;
        return $this;
    }

    protected function calculatePage(int $offset)
    {
        $pageCount = (int) ceil($this->recordCount / $this->itemsPerPage);
        $closerCount = $this->recordCount - $offset;

        if($closerCount < $this->itemsPerPage) {
            return $pageCount == 0 ? 1 : $pageCount;
        }

        if($offset < $this->itemsPerPage) return (int) 1;

        if($offset >= $this->itemsPerPage) {
            $page = (int) round($offset / $this->itemsPerPage, 0, PHP_ROUND_HALF_UP) + 1;

            return $page;
        }

        $page = 1;

        return $page;
    }
}