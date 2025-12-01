<?php

namespace InertiaResource\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface SearchQueryBuilder
{
    /**
     * Apply search query to the builder
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  string  $search The search term
     * @return Builder
     */
    public function apply(Builder $query, Request $request, string $search): Builder;
}

