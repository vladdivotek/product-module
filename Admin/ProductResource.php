<?php

namespace Modules\Product\Admin;

use Modules\Product\Admin\ProductResource\Pages;
use App\Filament\Resources\TranslateResource\RelationManagers\TranslatableRelationManager;
use App\Models\Setting;
use App\Services\Schema;
use App\Services\TableSchema;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Modules\Product\Admin\ProductResource\RelationManagers\CategoryRelationManager;
use Modules\Product\Admin\ProductResource\RelationManagers\ReviewsRelationManager;
use Modules\Product\Models\Product;
use Modules\Seo\Admin\SeoResource\Pages\SeoRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getModelLabel(): string
    {
        return __('Product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Schema::getReactiveName(),
                        Schema::getSlug(),
                        Schema::getSorting(),
                        Schema::getStatus(),
                        Schema::getSku(),
                        Schema::getPrice(),
                        Schema::getImage('images', isMultiple: true)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TableSchema::getName(),
                TableSchema::getSorting(),
                TableSchema::getStatus(),
                TableSchema::getSku(),
                TableSchema::getPrice(),
                TableSchema::getViews(),
                TableSchema::getUpdatedAt()
            ])
            ->headerActions([
                Action::make(__('Help'))
                    ->iconButton()
                    ->icon('heroicon-o-question-mark-circle')
                    ->modalDescription(__('Here you can manage blog categories. Blog categories are used to group blog articles. You can create, edit and delete blog categories as you want. Blog category will be displayed on the blog page or inside slider(modules section). If you want to disable it, you can do it by changing the status of the blog category.'))
                    ->modalFooterActions([]),

            ])
            ->reorderable('sorting')
            ->filters([
                TableSchema::getFilterStatus(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('View')
                    ->label(__('View'))
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) {
                        return $record->route();
                    })->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('Template')
                    ->slideOver()
                    ->icon('heroicon-o-cog')
                    ->fillForm(function (): array {
                        return [
                            'template' => setting(config('settings.product.template'), []),
                            'design' => setting(config('settings.product.design'), 'Zero')
                        ];
                    })
                    ->action(function (array $data): void {
                        setting([
                            config('settings.product.template') => $data['template'],
                            config('settings.product.design') => $data['design']
                        ]);
                        Setting::updatedSettings();
                    })
                    ->form(function ($form) {
                        return $form
                            ->schema([
                                Schema::getModuleTemplateSelect('Pages/Product'),
                                Section::make('')->schema([
                                    Schema::getTemplateBuilder()->label(__('Template')),
                                ]),
                            ]);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('Seo and translates', [
                TranslatableRelationManager::class,
                SeoRelationManager::class,
                ReviewsRelationManager::class,
                CategoryRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
