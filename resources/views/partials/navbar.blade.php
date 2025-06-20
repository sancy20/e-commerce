<nav 
    x-data="categoryNavComponent({{ Js::from($mainCategoriesWithSubcategories ?? []) }})"
    class="bg-white shadow-sm sticky top-0 z-30"
>
    {{-- TOP ROW: Logo, Search, Cart, Auth --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            {{-- Left Side: Logo --}}
            <div class="flex-shrink-0">
                <a href="/" class="font-bold text-xl text-gray-800">{{ config('app.name', 'Laravel') }}</a>
            </div>

            {{-- Center: Search Bar --}}
            <div class="flex-1 max-w-xl mx-4">
                <form action="{{ route('products.index') }}" method="GET" class="relative">
                    <input type="text" name="search" placeholder="Search for products..." value="{{ request('search') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <button type="submit" class="absolute inset-y-0 right-0 px-4 text-gray-600 hover:text-gray-900" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            {{-- Right Side: Auth and Cart Links --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-gray-700 mr-6">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart (<span x-text="$store.cart.count"></span>)</span>
                </a>
                @auth
                    <a href="{{ route('dashboard.index') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">{{ Auth::user()->name }}</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Sign in</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md">Create account</a>
                @endguest
            </div>
        </div>
    </div>

    {{-- BOTTOM ROW: Categories & Sell Link --}}
    <div class="bg-white border-t border-gray-200" @mouseleave="isOpen = false">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-12">
                {{-- Left Side Links --}}
                <div class="flex items-center h-full">
                    {{-- All Categories Mega Menu Dropdown --}}
                    <div @mouseenter="isOpen = 'categories'" class="relative h-full flex items-center">
                        <button class="inline-flex items-center px-1 h-full text-sm font-medium"
                                :class="isOpen === 'categories' ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900'">
                            <i class="fas fa-bars mr-2"></i>
                            <span>All Categories</span>
                        </button>
                    </div>

                    {{-- Featured Selections Dropdown --}}
                    <div @mouseenter="isOpen = 'featured'" class="relative h-full flex items-center ml-10">
                        <button class="inline-flex items-center px-1 h-full text-sm font-medium"
                                :class="isOpen === 'featured' ? 'text-blue-600' : 'text-gray-600 hover:text-gray-900'">
                            <span>Featured selections</span>
                        </button>
                    </div>
                </div>

                {{-- Right Side Link --}}
                @auth
                    @if (!Auth::user()->isVendor() && !Auth::user()->isPendingVendor())
                        <a href="{{ route('vendor_application.apply') }}" class="inline-flex items-center px-1 text-sm font-medium text-blue-600 hover:text-blue-800">Sell on Platform</a>
                    @endif
                @endauth
            </div>
        </div>

        {{-- UPDATE: This is the container for the full-width dropdown panels --}}
        <div 
            x-show="isOpen" 
            x-transition:enter="transition ease-out duration-200" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="transition ease-in duration-150" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0"
            class="absolute left-0 w-full bg-white shadow-lg border-t"
            style="display: none;"
        >
            {{-- Category Panel --}}
            <div x-show="isOpen === 'categories'" class="max-w-7xl mx-auto px-8">
                <div class="flex py-4">
                    <div class="w-64 border-r border-gray-200 py-2 pr-4">
                        <template x-for="category in allCategories" :key="category.id">
                            <div @mouseenter="activeMainCategory = category" :class="{'bg-gray-100': activeMainCategory && activeMainCategory.id === category.id}" class="flex justify-between items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 cursor-pointer">
                                <span x-text="category.name"></span><i class="fas fa-chevron-right h-4 w-4"></i>
                            </div>
                        </template>
                    </div>
                    <div class="flex-1 p-4">
                        <template x-if="activeMainCategory">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg" x-text="activeMainCategory.name"></h3>
                                <div class="grid grid-cols-3 gap-x-8 gap-y-4 mt-4">
                                    <template x-for="subCategory in activeMainCategory.children" :key="subCategory.id">
                                        <a :href="'{{ route('products.index') }}?category=' + subCategory.id" class="block text-sm text-gray-600 hover:text-blue-600 hover:underline" x-text="subCategory.name"></a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Featured Selections Panel --}}
            <div x-show="isOpen === 'featured'" class="max-w-7xl mx-auto px-8 py-6">
                <div class="grid grid-cols-3 gap-8">
                    {{-- UPDATE: Links now use ?filter=... instead of ?sort=... --}}
                    <a href="{{ route('products.index', ['filter' => 'top_rated']) }}" class="block p-6 text-center bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition">
                        <i class="fas fa-medal text-4xl text-yellow-500 mb-2"></i>
                        <p class="font-semibold text-gray-800">Top Ranking</p>
                    </a>
                    <a href="{{ route('products.index', ['filter' => 'new_arrivals']) }}" class="relative block p-6 text-center bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition">
                        <span class="absolute top-2 right-2 px-2 py-0.5 bg-green-500 text-white text-xs font-bold rounded-full">NEW</span>
                        <i class="fas fa-box-open text-4xl text-green-500 mb-2"></i>
                        <p class="font-semibold text-gray-800">New arrivals</p>
                    </a>
                    <a href="{{ route('products.index', ['filter' => 'top_deals']) }}" class="block p-6 text-center bg-gray-50 rounded-lg hover:bg-gray-100 hover:shadow-md transition">
                        <i class="fas fa-tag text-4xl text-red-500 mb-2"></i>
                        <p class="font-semibold text-gray-800">Top deals</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

@push('scripts')
<script>
    function categoryNavComponent(categories) {
        return {
            isOpen: false,
            allCategories: categories,
            activeMainCategory: null,
            init() {
                if (this.allCategories && this.allCategories.length > 0) {
                    this.activeMainCategory = this.allCategories[0];
                }
            }
        }
    }
</script>
@endpush