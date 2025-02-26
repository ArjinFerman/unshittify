<?php

namespace App\Domain\Core\Actions;

use App\Domain\Core\Models\Author;

class FindOrCreateAuthorAction extends BaseAction
{
    /**
     * @throws \Throwable
     */
    public function execute(string $name, array $data = []): Author
    {
        return $this->optionalTransaction(function () use ($name, $data) {
            $author = Author::whereName($name)->first();
            if (!$author) {
                $author = Author::create([
                    'name' => $name,
                    'description' => $data['description'],
                ]);
            }

            return $author;
        });
    }
}
