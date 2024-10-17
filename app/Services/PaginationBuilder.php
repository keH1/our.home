<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Sajya\Server\Exceptions\InvalidParams;
use Sajya\Server\Exceptions\RpcException;

class PaginationBuilder
{
    protected ?int $limit = null;
    protected ?int $offset = null;
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
        $maxLimit = config('pagination.max_limit', 15);

        $validator = Validator::make($request->all(), [
            'limit' => "sometimes|integer|min:1|max:$maxLimit",
            'offset' => 'required_with:limit|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new InvalidParams('Неверные параметры пагинации');
        }

        $limit = $request->has('limit') ? (int)$request->input('limit') : null;
        $offset = $request->has('limit') ? (int)$request->input('offset', 0) : null;

        return new self($limit, $offset);
    }

    /**
     * Конструктор PaginationBuilder.
     *
     * @param int|null $limit
     * @param int|null $offset
     */
    public function __construct(?int $limit, ?int $offset)
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
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Получает значение offset.
     *
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * Проверяет, активна ли пагинация.
     *
     * @return bool
     */
    public function isPaginationEnabled(): bool
    {
        return $this->limit !== null;
    }

    /**
     * Возвращает данные пагинации для ответа.
     *
     * @return array|null
     */
    public function toArray(): ?array
    {
        if (!$this->isPaginationEnabled()) {
            return null;
        }

        return [
            'total' => $this->total,
            'limit' => $this->limit,
            'offset' => $this->offset,
        ];
    }
}
