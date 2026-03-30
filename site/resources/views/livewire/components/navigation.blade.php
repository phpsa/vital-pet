<header class="relative border-b border-gray-100 ves-nav">
    <div class="flex items-center justify-between h-16 px-4 mx-auto max-w-screen-2xl sm:px-6 lg:px-8">
        <div class="flex items-center">
            <a class="flex items-center flex-shrink-0"
               href="{{ url('/') }}"
               wire:navigate
            >
                <span class="sr-only">Home</span>

                <x-brand.logo class="w-auto h-6 ves-logo" />
            </a>

            <nav class="hidden lg:gap-4 lg:flex lg:ml-8">
                @foreach ($this->collections as $collection)
                    <a class="text-sm font-medium transition hover:opacity-75 ves-nav-link"
                       href="{{ route('collection.view', $collection->defaultUrl->slug) }}"
                       wire:navigate
                    >
                        {{ $collection->translateAttribute('name') }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="flex items-center justify-between flex-1 ml-4 lg:justify-end">
            <x-header.search class="max-w-sm mr-4" />

            <div class="hidden items-center gap-3 lg:flex lg:mr-4">
                @auth
                    <div class="relative"
                         x-data="{ profileMenu: false }">
                        <button class="ves-profile-trigger"
                                type="button"
                                x-on:click="profileMenu = !profileMenu">
                            Profile

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-4 h-4"
                                 viewBox="0 0 20 20"
                                 fill="currentColor">
                                <path fill-rule="evenodd"
                                      d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z"
                                      clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-cloak
                             x-show="profileMenu"
                             x-transition
                             x-on:click.away="profileMenu = false"
                             class="absolute right-0 z-50 w-48 mt-3 overflow-hidden bg-white border border-gray-100 rounded-lg shadow-xl ves-menu-panel">
                            <a class="ves-profile-menu-item"
                               href="{{ route('account.orders') }}">
                                Orders
                            </a>

                            <a class="ves-profile-menu-item border-t border-gray-100"
                               href="{{ route('account.address-book') }}">
                                Address Book
                            </a>

                            <a class="ves-profile-menu-item border-t border-gray-100"
                               href="{{ route('account.security') }}">
                                Security
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="ves-profile-menu-item w-full text-left border-t border-gray-100"
                                        type="submit">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="text-sm font-medium transition hover:opacity-75 ves-nav-link"
                       href="{{ route('login') }}">
                        Login
                    </a>
                @endauth
            </div>

            <div class="flex items-center -mr-4 sm:-mr-6 lg:mr-0">
                @livewire('components.cart')

                <div x-data="{ mobileMenu: false }">
                        <button x-on:click="mobileMenu = !mobileMenu"
                            class="grid flex-shrink-0 w-16 h-16 border-l border-gray-100 lg:hidden ves-icon-button">
                        <span class="sr-only">Toggle Menu</span>

                        <span class="place-self-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </span>
                    </button>

                    <div x-cloak
                         x-transition
                         x-show="mobileMenu"
                         class="absolute right-0 top-auto z-50 w-screen p-4 sm:max-w-xs">
                        <ul x-on:click.away="mobileMenu = false"
                            class="p-6 space-y-4 bg-white border border-gray-100 shadow-xl rounded-xl ves-menu-panel">
                            @foreach ($this->collections as $collection)
                                <li>
                                    <a class="text-sm font-medium ves-nav-link"
                                       href="{{ route('collection.view', $collection->defaultUrl->slug) }}"
                                       wire:navigate
                                    >
                                        {{ $collection->translateAttribute('name') }}
                                    </a>
                                </li>
                            @endforeach

                            @auth
                                <li class="border-t border-gray-100 pt-4">
                                    <a class="text-sm font-medium ves-nav-link"
                                       href="{{ route('account.orders') }}">
                                        Orders
                                    </a>
                                </li>
                                <li>
                                    <a class="text-sm font-medium ves-nav-link"
                                       href="{{ route('account.address-book') }}">
                                        Address Book
                                    </a>
                                </li>
                                <li>
                                    <a class="text-sm font-medium ves-nav-link"
                                       href="{{ route('account.security') }}">
                                        Security
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="text-sm font-medium ves-nav-link"
                                                type="submit">
                                            Logout
                                        </button>
                                    </form>
                                </li>
                            @else
                                <li class="border-t border-gray-100 pt-4">
                                    <a class="text-sm font-medium ves-nav-link"
                                       href="{{ route('login') }}">
                                        Login
                                    </a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
