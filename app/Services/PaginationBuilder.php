<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\RpcException;

class PaginationBuilder
{
    protected int $limit;
    protected int $offset;
    protected int $total = 0;

    /**
     * Создает экземпляр PaginationBuilder на основе запроса.
     *
     * @param Request $request
     * @return static
     * @throws RpcException
     */
    public static function fromRequest(Request $request): self
    {
        $defaultLimit = config('pagination.default_limit', 15);
        $maxLimit = config('pagination.max_limit', 100);

        $validator = Validator::make($request->all(), [
            'limit' => "sometimes|integer|min:1|max:$maxLimit",
            'offset' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams('Неверные параметры пагинации');
        }

        $limit = $request['limit'] ?? $defaultLimit;
        $offset = $request['offset'] ?? 0;

        return new self($limit, $offset);
    }

    /**
     * Конструктор PaginationBuilder.
     *
     * @param int $limit
     * @param int $offset
     */
    public function __construct(int $limit, int $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Устанавливает общее количество элементов.
     *
     * @param int $total
     * @return $this
     */
    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Получает значение limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Получает значение offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Возвращает данные пагинации для ответа.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
