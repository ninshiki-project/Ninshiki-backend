<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShopResource\Pages;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class ShopResource extends Resource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationParentItem = 'Store';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                FileUpload::make('image')
                    ->visibleOn('view')
                    ->image(),
                Forms\Components\Select::make('product_id')
                    ->native(false)
                    ->preload()
                    ->searchable()
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->visibleOn('view'),
                Forms\Components\TextInput::make('stock')
                    ->visibleOn('view'),
                Forms\Components\TextInput::make('price')
                    ->visibleOn('view'),
                Forms\Components\TextInput::make('status')
                    ->visibleOn('view'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->mutateRecordDataUsing(function (array $data, Shop $shop): array {
                        $data['name'] = $shop->product->name;
                        $data['stock'] = $shop->product->stock;
                        $data['price'] = $shop->product->price;
                        $data['status'] = $shop->product->status;
                        $data['image'] = $shop->product->image;

                        return $data;
                    })
                    ->modalFooterActionsAlignment(Alignment::Right)
                    ->modalWidth(MaxWidth::Small)
                    ->modalAlignment(Alignment::Center),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShops::route('/'),
        ];
    }
}
