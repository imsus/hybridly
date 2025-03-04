<?php

namespace Hybridly\Tables\Concerns;

use Hybridly\Refining\Contracts\Refiner;
use Hybridly\Refining\Refine;
use Hybridly\Support\Configuration\Configuration;
use Hybridly\Tables\Columns\BaseColumn;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Data;

trait RefinesAndPaginatesRecords
{
    private null|Refine $refine = null;
    private mixed $cachedRecords = null;
    private mixed $cachedRefiners = null;

    public function getRefiners(): Collection
    {
        return $this->cachedRefiners ??= collect($this->defineRefiners())
            ->filter(static fn (Refiner $refiner): bool => !$refiner->isHidden());
    }

    public function getRecords(): array
    {
        return data_get($this->getPaginatedRecords(), 'data', []);
    }

    public function getRefinedQuery(): Builder
    {
        return $this->getRefineInstance()->getBuilderInstance();
    }

    /**
     * Disables authorization resolving.
     */
    public function withoutResolvingAuthorizations(): static
    {
        $this->resolvesAuthorizations = false;

        return $this;
    }

    protected function defineRefiners(): array
    {
        return [];
    }

    protected function getPaginatorMeta(): array
    {
        $pagination = $this->getPaginatedRecords();

        // Wraps pagination data if necessary
        if (!\array_key_exists('meta', $pagination)) {
            return [
                'links' => $pagination['links'] ?? [],
                'meta' => array_filter([
                    'current_page' => $pagination['current_page'] ?? null,
                    'first_page_url' => $pagination['first_page_url'] ?? null,
                    'from' => $pagination['from'] ?? null,
                    'last_page' => $pagination['last_page'] ?? null,
                    'last_page_url' => $pagination['last_page_url'] ?? null,
                    'next_page_url' => $pagination['next_page_url'] ?? null,
                    'path' => $pagination['path'] ?? null,
                    'per_page' => $pagination['per_page'] ?? null,
                    'prev_page_url' => $pagination['prev_page_url'] ?? null,
                    'to' => $pagination['to'] ?? null,
                    'total' => $pagination['total'] ?? null,
                    'prev_cursor' => $pagination['prev_cursor'] ?? null,
                    'next_cursor' => $pagination['next_cursor'] ?? null,
                ]),
            ];
        }

        return [
            'links' => $pagination['links'],
            'meta' => $pagination['meta'],
        ];
    }

    protected function getRequest(): Request
    {
        return $this->getRefineInstance()->getRequest();
    }

    protected function transformRecords(Paginator|CursorPaginator $paginator): Paginator|CursorPaginator|BaseDataCollectable
    {
        return $paginator;
    }

    /**
     * Determines how the query will be paginated.
     */
    protected function paginateRecords(Builder $query): Paginator|CursorPaginator
    {
        $paginator = match ($this->getPaginatorType()) {
            LengthAwarePaginator::class => $query->paginate(
                perPage: $this->getRecordsPerPage(),
                pageName: $this->formatScope('page'),
            ),
            Paginator::class => $query->simplePaginate(
                perPage: $this->getRecordsPerPage(),
                pageName: $this->formatScope('page'),
            ),
            CursorPaginator::class => $query->cursorPaginate(
                perPage: $this->getRecordsPerPage(),
                cursorName: $this->formatScope('cursor'),
            ),
            default => throw new \Exception("Invalid paginator type [{$this->getPaginatorType()}]"),
        };

        return $paginator->withQueryString();
    }

    /**
     * Defines the kind of pagination that will be used for this table.
     */
    protected function getPaginatorType(): string
    {
        return $this->paginatorType ?? LengthAwarePaginator::class;
    }

    /**
     * Defines the amount of records per page for this table.
     */
    protected function getRecordsPerPage(): int
    {
        return $this->recordsPerPage ?? 10;
    }

    protected function transformRefinements(Refine $refining): void
    {
        //
    }

    protected function getRefineInstance(): Refine
    {
        if (!$this->refine) {
            $this->refine = Refine::query($this->defineQuery())
                ->scope($this->getScope())
                ->with($this->getRefiners());

            $this->transformRefinements($this->refine);
        }

        return $this->refine->applyRefiners();
    }

    protected function getRefinements(): array
    {
        return $this->getRefineInstance()->refinements();
    }

    protected function getRecordFromModel(Model $model): array|Data
    {
        if (isset($this->data) && is_a($this->data, Data::class, allow_string: true)) {
            $record = $this->resolveDataRecord($model);

            if (!$this->resolvesAuthorizations()) {
                $record->excludePermanently('authorization');
            }

            return $record->toArray();
        }

        return $model->toArray();
    }

    protected function resolveDataRecord(Model $model): Data
    {
        return $this->data::from($model);
    }

    /**
     * Determines whether authorizations should be resolved.
     */
    protected function resolvesAuthorizations(): bool
    {
        return $this->resolvesAuthorizations ?? true;
    }

    private function getPaginatedRecords(): array
    {
        return $this->cachedRecords ??= $this->transformPaginatedRecords()->toArray();
    }

    private function transformPaginatedRecords(): Paginator|CursorPaginator|BaseDataCollectable
    {
        $paginatedRecords = $this->paginateRecords($this->getRefinedQuery());

        /** @var Collection<BaseColumn> */
        $columns = $this->getTableColumns()->mapWithKeys(static fn (BaseColumn $column) => [$column->getName() => $column]);

        $keyName = $this->getKeyName();
        $modelClass = $this->getModelClass();

        // These are the columns we may include, if requested, in the record object.
        $columnsToInclude = [...$columns->keys(), $keyName, '__hybridId', 'authorization'];

        // We need to know if the record key is included in the columns, because it may be used for actions.
        // If it's included but transformed, we consider it's not included and we will force-include it.
        $hasKeyAsColumn = $columns->has($keyName) && !$columns->get($keyName)->canTransformValue();

        // If we need the original record ID for actions, we may force-include it if it's not already in the columns.
        $forceIncludeOriginalRecordId = Configuration::get()->tables->enableActions && !$hasKeyAsColumn;

        return $paginatedRecords->through(function (Model $model, int $fakeId) use ($forceIncludeOriginalRecordId, $hasKeyAsColumn, $modelClass, $columns, $columnsToInclude) {
            $record = $this->getRecordFromModel($model);

            // If actions are enabled but the record's key is not included in the
            // columns or is transformed, ensure we still return it because
            // it is needed to identify records when performing actions
            if (!$hasKeyAsColumn) {
                $record['__hybridId'] = $forceIncludeOriginalRecordId
                    ? $model->getKey()
                    : $fakeId;
            }

            return collect($columnsToInclude)
                ->mapWithKeys(static function (string $key) use ($columns, $model, $record, $modelClass) {
                    /** @var ?BaseColumn */
                    $column = $columns[$key] ?? null;
                    $value = $record[$key] ?? null;

                    // These are special columns that shouldn't be nested as a {value, extra} object.
                    if (\in_array($key, ['__hybridId', 'authorization'], strict: true)) {
                        return [$key => $value];
                    }

                    // If we don't have a column for this property, we don't send it to the front-end.
                    if (!$columns->has($key)) {
                        return [];
                    }

                    return [
                        $key => [
                            'extra' => \is_null($column) || !$column->hasExtra()
                                ? []
                                : $column->getExtra(
                                    named: [
                                        'record' => $model,
                                        'model' => $model,
                                    ],
                                    typed: [
                                        $modelClass => $model,
                                    ],
                                ),
                            'value' => \is_null($column) || !$column->canTransformValue()
                                ? $value
                                : $column->getTransformedValue(
                                    named: [
                                        'column' => $column,
                                        'record' => $record,
                                    ],
                                    typed: [
                                        $modelClass => $model,
                                    ],
                                ),
                        ],
                    ];
                })
                ->filter(fn (mixed $value, string $key) => \in_array($key, $columnsToInclude, strict: true) && !\is_null($value));
        });
    }
}
