<?php

namespace AppBundle\Pagination;

class PaginatedCollection
{
    private $items;

    private $total;

    private $count;

    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);
    }
}
