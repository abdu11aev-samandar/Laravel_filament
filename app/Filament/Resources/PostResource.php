<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\BelongsToSelect::make('category_id')
                    ->relationship('category', 'name'),

                Forms\Components\Card::make()->schema([
                    Forms\Components\TextInput::make('title')->reactive()
                        ->afterStateUpdated(function (\Closure $set, $state) {
                            $set('slug', Str::slug($state));
                        })->required(),

                    Forms\Components\TextInput::make('slug')->required(),

//                    SpatieMediaLibraryFileUpload::make('thumbnail')
//                        ->collection('posts'),

                    Forms\Components\RichEditor::make('content'),

                    Forms\Components\Toggle::make('is_published')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')->limit('50')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('slug')->limit('50'),
                Tables\Columns\BooleanColumn::make('is_published'),
//                Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')->collection('posts')
            ])
            ->filters([
                Tables\Filters\Filter::make('Published')
                    ->query(fn(Builder $query): Builder => $query->where('is_published', true)),
                Tables\Filters\Filter::make('UnPublished')
                    ->query(fn(Builder $query): Builder => $query->where('is_published', false)),
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TagsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
