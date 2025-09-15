<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start md:flex-row justify-between md:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                {{ __('Links') }}
            </h2>
            <a href="{{ route('links.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                {{ __('Create Link') }}
            </a>
        </div>
    </x-slot>

    <div class="py-0 md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="table-links">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 hidden md:table-header-group">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[40%]">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Short Link
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Visits
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        QR Code
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach (auth()->user()->links as $link)
                                    <tr class="md:table-row">
                                        <td class="px-0 md:px-6 py-4 md:table-cell w-[40%]">
                                            <div class="flex items-center hidden md:flex">
                                                <div class="w-[100px] h-[100px] rounded-[5px] mr-4 overflow-hidden flex-shrink-0">
                                                    @if ($link->image)
                                                        <img src="{{ asset($link->image) }}" alt="{{ $link->name }}" class="meta-image w-full h-full object-cover text-center text-xs text-gray-400 bg-gray-200 flex justify-center items-center before:p-2">
                                                        <!--  onerror="this.onerror=null;this.src='https://placehold.co/100?text={{ urlencode(preg_replace("/ /", "\n", $link->name)) }}';" -->
                                                    @else
                                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L20 16m-2-6a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a href="{{ $link->original_url }}" target="_blank" class="font-bold text-sm text-gray-900 underline">{{ $link->name }}</a>
                                                   <div class="text-xs text-gray-500">{{ $link->description }}</div>
                                                   <div class="mt-2">
                                                       @foreach ($link->categories as $category)
                                                           <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-2">{{ $category->name }}</span>
                                                       @endforeach
                                                   </div>
                                                </div>
                                            </div>
                                            <div class="block md:hidden">
                                                <div class="flex mb-2">
                                                    <div class="w-[100px] h-[100px] rounded-[5px] mr-4 overflow-hidden flex-shrink-0">
                                                        @if ($link->image)
                                                            <img src="{{ asset($link->image) }}" alt="{{ $link->name }}" class="w-full h-full object-cover">
                                                        @else
                                                            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L20 16m-2-6a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                   <button
                                                       x-data=""
                                                       x-on:click.prevent="$dispatch('open-modal', 'qr-code-{{ $link->id }}')"
                                                       class="w-[100px] h-[100px] visible-print text-center"
                                                   >
                                                        @php
                                                            $qr = new SimpleSoftwareIO\QrCode\Generator;
                                                            $svg = $qr->size(100)->margin(1)->errorCorrection('M')->generate(url($link->short_code));
                                                            echo $svg;
                                                        @endphp
                                                    </button>
                                                </div>
                                                <div class="mb-2">
                                                    <a href="{{ $link->original_url }}" target="_blank" class="font-bold text-gray-900 underline">{{ $link->name }}</a>
                                                </div>
                                               <div class="mb-2 text-sm text-gray-500">
                                                   {{ $link->description }}
                                               </div>
                                               <div class="mt-2">
                                                   @foreach ($link->categories as $category)
                                                       <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-xs font-semibold text-gray-700 mr-2">{{ $category->name }}</span>
                                                   @endforeach
                                               </div>
                                                <div class="mb-2">
                                                    <span class="font-bold">Short Link:</span><br/> <span class="break-all"><a href="{{ $link->short_code }}" target="_blank" class="text-sm hover:underline">{{ url($link->short_code) }}</a></span>
                                                </div>
                                                <div class="mb-2">
                                                    <span class="font-bold">Visits:</span> {{ $link->visits }}
                                                </div>
                                                <div class="text-left">
                                                    <div class="flex">
                                                        <x-secondary-button
                                                            tag="a"
                                                            href="{{ route('links.edit', $link) }}"
                                                            rounding="rounded-l-md"
                                                        >{{ __('Edit') }}</x-secondary-button>
                                                        <x-danger-button
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-link-deletion'); $dispatch('set-delete-action', '{{ route('links.destroy', $link) }}')"
                                                            rounding="rounded-r-md"
                                                        >{{ __('Delete') }}</x-danger-button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden md:table-cell px-6 py-4">
                                            <div class="text-sm text-gray-900 break-all"><a href="{{ $link->short_code }}" target="_blank" class="hover:underline">{{ url($link->short_code) }}</a></span></div>
                                        </td>
                                        <td class="hidden md:table-cell px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $link->visits }}</div>
                                        </td>
                                        <td class="hidden md:table-cell px-6 py-4">
                                           <button
                                               x-data=""
                                               x-on:click.prevent="$dispatch('open-modal', 'qr-code-{{ $link->id }}')"
                                               class="visible-print text-center"
                                           >
                                                {{ $svg }}
                                            </button>
                                        </td>
                                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex">
                                                <x-secondary-button
                                                    tag="a"
                                                    href="{{ route('links.edit', $link) }}"
                                                    rounding="rounded-l-md"
                                                >{{ __('Edit') }}</x-secondary-button>
                                                <x-danger-button
                                                    x-data=""
                                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-link-deletion'); $dispatch('set-delete-action', '{{ route('links.destroy', $link) }}')"
                                                    rounding="rounded-r-md"
                                                >{{ __('Delete') }}</x-danger-button>
                                            </div>
                                        </td>
                                    </tr>
                                    <x-modal name="qr-code-{{ $link->id }}" :show="false" focusable>
                                        <div class="p-6" id="qr-code-modal-{{ $link->id }}">
                                            <div class="flex justify-center">
                                                @php
                                                    $qr = new SimpleSoftwareIO\QrCode\Generator;
                                                    $svg = $qr->size(300)->margin(1)->errorCorrection('M')->generate(url($link->short_code));
                                                    echo $svg;
                                                @endphp
                                            </div>
                                            <div class="mt-4 text-center">
                                                <a href="{{ url($link->short_code) }}" target="_blank" class="text-sm text-gray-500 hover:underline">{{ url($link->short_code) }}</a>
                                            </div>
                                            <div class="mt-6 flex justify-end">
                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                    {{ __('Close') }}
                                                </x-secondary-button>
                                                <x-primary-button class="ms-3" onclick="downloadQrCode('{{ $link->id }}', '{{ $link->short_code }}')">
                                                    {{ __('Download') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </x-modal>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="confirm-link-deletion" :show="$errors->linkDeletion->isNotEmpty()" focusable>
        <form method="post" x-data="{ action: '' }" x-on:set-delete-action.window="action = $event.detail" :action="action" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete this link?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once this link is deleted, all of its resources and data will be permanently deleted.') }}
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Link') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
        function downloadQrCode(linkId, shortCode) {
            const svg = document.querySelector('#qr-code-modal-' + linkId + ' svg');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = () => {
                canvas.width = 500;
                canvas.height = 500;
                ctx.drawImage(img, 0, 0, 500, 500);
                const a = document.createElement('a');
                a.href = canvas.toDataURL('image/jpeg');
                a.download = shortCode + '.jpg';
                a.click();
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svg.outerHTML);
        }
    </script>
    @endpush
</x-app-layout>