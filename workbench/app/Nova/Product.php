<?php

namespace Workbench\App\Nova;

use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Workbench\App\Models\Product as Model;

/**
 * @extends Resource<Model>
 */
class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static string $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'name', 'description',
    ];

    /**
     * Get the label for the resource.
     */
    final public static function label(): string
    {
        return 'Products';
    }

    /**
     * Get the singular label for the resource.
     */
    final public static function singularLabel(): string
    {
        return 'Product';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    final public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules(fn (): array => $this->model()?->validationRules['name'] ?? []),

            Textarea::make('Description')
                ->rules(fn (): array => $this->model()?->validationRules['description'] ?? []),

            Currency::make('Price')
                ->currency('USD')
                ->sortable()
                ->rules(fn (): array => $this->model()?->validationRules['price'] ?? []),

            Number::make('Stock')
                ->sortable()
                ->filterable()
                ->rules(fn (): array => $this->model()?->validationRules['stock'] ?? []),

            DateTime::make('Created At')
                ->exceptOnForms()
                ->sortable(),

            DateTime::make('Updated At')
                ->exceptOnForms()
                ->sortable(),
        ];
    }
}
