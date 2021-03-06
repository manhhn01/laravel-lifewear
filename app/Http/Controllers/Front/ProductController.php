<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ReviewStoreRequest;
use App\Http\Resources\Front\Collections\ProductPaginationCollection;
use App\Http\Resources\Front\ProductIndexResource;
use App\Http\Resources\Front\ProductShowResource;
use App\Http\Resources\Front\ReviewResource;
use App\Models\Product;
use App\Repositories\Products\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    protected $productRepo;
    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function index(Request $request)
    {
        $filterNames = ['color', 'category_id', 'size', 'price_min', 'price_max'];
        return new ProductPaginationCollection(
            $this->productRepo->filterAndPage(
                $request->only($filterNames),
                $request->query('perpage', 30),
                $request->query('sortby', 'created_at'),
                $request->query('order', 'desc')
            )
        );
        // Product::with('variants')
        //     ->status(1)
        //     ->orderBy('created_at', 'desc')
        //     ->paginate($request->query('perpage', 30))
    }

    public function topProducts()
    {
        return new ProductPaginationCollection(
            //todo
            Cache::remember('top_product', 18000, function () {
                // return Product::with(['variants'] =>)
                //     ->withCount('variants.orders')
                //     ->status(1)
                //     ->orderBy();
            })
        );
    }

    public function search(Request $request)
    {
        if (!empty($query = $request->query('q'))) {
            $products = Product::search($query)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('perpage', 30))
                ->tap(function ($products) {
                    $products
                        ->makeHidden(['reviews', 'publicReviews'])
                        ->load('variants')
                        ->loadCount('publicReviews');
                });
        }

        return new ProductPaginationCollection($products ?? (new LengthAwarePaginator([], 0, 30)));
    }

    public function show($id_slug)
    {
        $product = $this->productRepo->findByIdOrSlug($id_slug);

        if ($product->status == 1)
            return new ProductShowResource(
                $product
                    ->load('images', 'categoryWithParent', 'publicReviews.user', 'publicReviews.likes', 'variants', 'tags')
            );
        else
            throw new NotFoundHttpException('Product not found');
    }

    public function relatedProducts($id_slug)
    {
        $product = $this->productRepo->findByIdOrSlug($id_slug);

        return ProductIndexResource::collection($this->productRepo->relatedProducts($product));
    }

    public function productReviews($id_slug)
    {
        $product = $this->productRepo->findByIdOrSlug($id_slug);

        return ReviewResource::collection($product->publicReviews);
    }

    public function likeReview(Request $request, $id_slug, $review_id)
    {
        $user = $request->user();
        $product = $this->productRepo->findByIdOrSlug($id_slug);
        $review = $product->reviews()->findOrFail($review_id);

        if ($request->like) {
            $review->likes()->syncWithoutDetaching($user->id);
        } else {
            $review->likes()->detach($user->id);
        }

        return new ReviewResource($review);
    }

    public function storeReview(ReviewStoreRequest $request, $id_slug)
    {
        $user = $request->user();
        $product = $this->productRepo->findByIdOrSlug($id_slug);

        if (!$user->hasBought($product)) {
            throw ValidationException::withMessages(['review.bought' => 'You haven\'t bought this product.']);
        }

        if ($user->hasReviewed($product)) {
            throw ValidationException::withMessages(['review.reviewed' => 'You\'ve reviewed this product before.']);
        }

        $attributes = $request->only(['comment', 'rating']);
        $attributes = array_merge($attributes, ['user_id' => $user->id]);
        $review = $product->reviews()->create($attributes);

        return new ReviewResource($review);
    }

    public function updateReview(Request $request)
    {
    }

    public function destroyReview(Request $request)
    {
    }
}
