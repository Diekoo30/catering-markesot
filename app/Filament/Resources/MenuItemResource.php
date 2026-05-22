<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuItemResource\Pages;
use App\Models\Category;
use App\Models\MenuItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Get;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Menu Makanan';
    protected static ?string $modelLabel = 'Menu';
    protected static ?string $pluralModelLabel = 'Menu Makanan';
    protected static string|\UnitEnum|null $navigationGroup = 'Kelola Data';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Menu')->schema([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->live(),

                TextInput::make('name')
                    ->label('Nama Menu')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])->columns(2),

            Section::make('Harga & Satuan')->schema([
                TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),

                TextInput::make('unit')
                    ->label('Satuan')
                    ->required()
                    ->default('porsi')
                    ->maxLength(50),

                TextInput::make('min_order_qty')
                    ->label('Min. Pemesanan')
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ])->columns(3),

            Section::make('Gambar & Status')->schema([
                FileUpload::make('image')
                    ->label('Foto Menu')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/heic'])
                    ->imageEditor()
                    ->disk('public')
                    ->directory('menu-images')
                    ->visibility('public')
                    ->columnSpanFull(),

                Toggle::make('is_available')
                    ->label('Tersedia')
                    ->default(true),
            ])->columns(2),

            Section::make('Skor AHP (Rekomendasi)')
                ->visible(function ($get) {
                    $categoryId = $get('category_id');
                    if (!$categoryId) {
                        return false;
                    }
                    $category = Category::find($categoryId);
                    return $category ? (bool) $category->enable_ahp_recommendation : false;
                })
                ->schema([
                    Select::make('skor_rasa')
                        ->label('Skor Rasa')
                        ->options([
                            '1.0' => 'Rasa Ringan / Tidak Pedas (1.0)',
                            '1.2' => 'Rasa Ringan / Gurih Ringan (1.2)',
                            '1.4' => 'Gurih Segar / Sedikit Pedas (1.4)',
                            '2.0' => 'Gurih Segar / Sedang (2.0)',
                            '2.5' => 'Pedas Gurih / Cukup Kuat (2.5)',
                            '3.0' => 'Pedas Kuat / Sangat Pedas (3.0)',
                        ])
                        ->default(1.0)
                        ->required(),

                    Select::make('skor_nutrisi')
                        ->label('Skor Nutrisi')
                        ->options([
                            '1.0' => 'Dominan Karbohidrat / Gizi Ringan (1.0)',
                            '2.0' => 'Protein Nabati & Telur / Gizi Sedang (2.0)',
                            '2.2' => 'Protein Sedang (Telur & Sayur) (2.2)',
                            '2.4' => 'Protein Tinggi (Ayam Kampung) (2.4)',
                            '3.0' => 'Protein Sangat Tinggi (Daging Sapi) (3.0)',
                        ])
                        ->default(1.0)
                        ->required(),

                    Select::make('skor_jenis_hidangan')
                        ->label('Skor Jenis Hidangan')
                        ->options([
                            '1.0' => 'Sajian Kuah Bening Ringan (1.0)',
                            '1.5' => 'Sajian Kering / Gorengan (1.5)',
                            '2.0' => 'Sajian Campuran / Basah / Nyemek (2.0)',
                            '2.4' => 'Sajian Berkuah Hangat (2.4)',
                            '3.0' => 'Kering Mutlak / Goreng Garing (3.0)',
                        ])
                        ->default(1.0)
                        ->required(),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=Menu&background=f59e0b&color=fff'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean(),
            ])
            ->paginationPageOptions([10, 25, 50, 100, 'all'])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Tersedia'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMenuItems::route('/'),
            'create' => Pages\CreateMenuItem::route('/create'),
            'edit'   => Pages\EditMenuItem::route('/{record}/edit'),
        ];
    }
}
