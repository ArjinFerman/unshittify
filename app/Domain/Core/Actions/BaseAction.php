<?php

namespace App\Domain\Core\Actions;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseAction
{
    protected bool $withTransaction = true;

    public static function make(): static
    {
        return app(static::class);
    }

    public function withoutTransaction(): static
    {
        $this->withTransaction = false;
        return $this;
    }

    public function withTransaction(): static
    {
        $this->withTransaction = true;
        return $this;
    }

    /**
     * @return Closure|false|mixed
     * @throws Throwable
     */
    protected function optionalTransaction(Closure $closure)
    {
        if (!$this->withTransaction) {
            return call_user_func($closure);
        }
        try {
            DB::beginTransaction();
            $result = call_user_func($closure);
            DB::commit();
            return $result;
        } catch (Throwable $e) {
            Log::error($e);
            DB::rollBack();
            throw $e;
        }
    }
}
