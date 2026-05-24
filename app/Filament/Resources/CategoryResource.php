<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategori Menu';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori';
    protected static string|\UnitEnum|null $navigationGroup = 'Kelola Data';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')->schema([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->default('porsi')
                    ->maxLength(50),

                Toggle::make('is_active')
                    ->label('Tampilkan Kategori')
                    ->helperText('Jika dimatikan, kategori ini tidak akan muncul di halaman publik dan tidak akan dipertimbangkan dalam rekomendasi SPK.')
                    ->default(true),

                Toggle::make('enable_ahp_recommendation')
                    ->label('Gunakan dalam SPK')
                    ->helperText('Jika diaktifkan, semua menu dalam kategori ini akan dipertimbangkan pada perhitungan SPK.')
                    ->default(false)
                    ->live()
                    ->hidden(fn (Get $get): bool => (bool) $get('enable_cross_sell'))
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $set('enable_cross_sell', false);
                        }
                    }),

                Toggle::make('enable_cross_sell')
                    ->label('Rekomendasi Menu Pelengkap (Cross-selling)')
                    ->helperText('Aktifkan jika menu dalam kategori ini disarankan sebagai menu pelengkap (opsional)')
                    ->default(false)
                    ->live()
                    ->hidden(fn (Get $get): bool => (bool) $get('enable_ahp_recommendation'))
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $set('enable_ahp_recommendation', false);
                        }
                    }),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),


                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\IconColumn::make('enable_ahp_recommendation')
                    ->label('Rekomendasi AHP')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('enable_cross_sell')
                    ->label('Menu Pelengkap')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginationPageOptions([10, 25, 50, 100, 'all'])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
