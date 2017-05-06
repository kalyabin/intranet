<?php

namespace HttpHelperBundle\Response;


use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * JSON для постраничной навигации
 *
 * @package HttpHelperBundle\Response
 */
class ListJsonResponse extends JsonResponse
{
    public function __construct(array $list, int $pageSize, int $pageNum, int $totalCount)
    {
        parent::__construct([
            'list' => $list,
            'pageSize' => $pageSize,
            'pageNum' => $pageNum,
            'totalCount' => $totalCount
        ]);
    }
}
