<?php

namespace App\Services;

class ApiResponseBuilder
{
    protected $data = null;
    protected ?string $message = null;
    protected ?PaginationBuilder $pagination = null;

    /**
     * Устанавливает данные ответа.
     *
     * @param mixed $data
     * @return $this
     */
    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Устанавливает сообщение ответа.
     *
     * @param string|null $message
     * @return $this
     */
    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Устанавливает объект пагинации.
     *
     * @param PaginationBuilder|null $pagination
     * @return $this
     */
    public function setPagination(?PaginationBuilder $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }

    /**
     * Формирует финальный ответ.
     *
     * @return array
     */
    public function build(): array
    {
        $response = [];

        $response['data'] = $this->data ?? [];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if ($this->pagination !== null) {
            $response['pagination'] = $this->pagination->toArray();
        }

        return $response;
    }
}
