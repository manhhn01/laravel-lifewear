<?php

namespace App\Repositories\Categories;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Cache;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function getModel()
    {
        return Category::class;
    }

    public function getProductsPage($amount, $category_id, $filter = null)
    {
        return Product::where('category_id', $category_id)->ofType($filter)->paginate($amount);
    }

    public function allRoot()
    {
        return $this->model->whereDoesntHave('parent')->get();
    }

    public function allRootWithChildren()
    {
        return $this->model->whereDoesntHave('parent')->with('children')->get();
    }

    public function allProducts($category)
    {
        return Cache::remember($category->id . '_products', 1800, function () use ($category) {
            $categoryIds = $category->children->pluck('id')->merge([$category->id]);
            return Product::status(1)
                ->with('variants')
                ->whereIn('category_id', $categoryIds)->get();
        });
    }

    public function allProductsPage($category, $filters, $perPage = 30, $sortBy = 'created_at', $order = 'desc', $onlyPublic = true)
    {
        $categoryIds = $category->children->pluck('id')->merge([$category->id]);
        $products = Product::status($onlyPublic ? 1 : 0)
            ->with('variants')
            ->whereIn('category_id', $categoryIds);

        if ($sortBy == 'price') {
            $products->orderByRaw('COALESCE(sale_price, price) ' . (strtolower($order) == 'asc' ? 'ASC' : 'desc'));
        } else {
            $products->orderBy($sortBy, $order);
        }

        if (!empty($filters)) {
            $products = $this->productsFilter($products, $filters);
        }

        return tap($category, function ($cat) use ($products, $perPage) {
            $productPage = $products->paginate($perPage);
            $cat->setAttribute('products', $productPage);
        });
    }

    public function productsFilter($productsQuery, $filters)
    {
        if (isset($filters['price_min']))
            $productsQuery
                ->whereNested(function ($q) use ($filters) {
                    $q
                        ->where(function ($q) use ($filters) {
                            return $q
                                ->whereNotNull('sale_price')
                                ->where('sale_price', '>', $filters['price_min']);
                        })
                        ->orWhere(function ($q) use ($filters) {
                            return $q
                                ->whereNull('sale_price')
                                ->where('price', '>', $filters['price_min']);
                        });
                });

        if (isset($filters['price_max']))
            $productsQuery
                ->whereNested(function ($q) use ($filters) {
                    $q
                        ->where(function ($q) use ($filters) {
                            return $q
                                ->whereNotNull('sale_price')
                                ->where('sale_price', '<', $filters['price_max']);
                        })
                        ->orWhere(function ($q) use ($filters) {
                            return $q
                                ->whereNull('sale_price')
                                ->where('price', '<', $filters['price_max']);
                        });
                });

        if (isset($filters['color'])) {
            $colorTags = explode(',', $filters['color']);
            $productsQuery
                ->whereHas('tags', function ($q) use ($colorTags) {
                    return $q->whereIn('tags.name', $colorTags);
                });
        }

        if (isset($filters['size'])) {
            $sizeNames = explode(',', $filters['size']);
            $productsQuery->whereHas('variants', function ($q) use ($sizeNames) {
                $q->whereHas('size', function ($q) use ($sizeNames) {
                    $q->whereIn('sizes.name', $sizeNames);
                });
            });
        }

        if (isset($filters['category_id'])) {
            $productsQuery = $productsQuery->where('category_id', $filters['category_id']);
        }

        return $productsQuery;
    }
}
