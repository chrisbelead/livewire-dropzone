<div
        x-cloak
        x-data="dropzone({
        _this: @this,
        uuid: @js($uuid),
    })"
        @dragleave.prevent="isDragging = false"
        @dragover.prevent="isDragging = true"
        @drop.prevent="onDrop"
        @js($uuid . ':uploadError').window="onUploadError"
        class="dz-block dz-antialiased"
>
    <div class="dz-flex dz-flex-col dz-items-start dz-h-full dz-w-full dz-justify-center dz-bg-transparent dz-dark:border-gray-600 dz-dark:hover:border-gray-500">
        @if(! is_null($error))
            <div class="dz-bg-red-50 dz-p-4 dz-w-full dz-mb-4 dz-rounded dz-dark:bg-red-600">
                <div class="dz-flex dz-gap-3 dz-items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dz-w-5 dz-h-5 dz-text-red-400 dz-dark:text-red-200">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <h3 class="dz-text-sm dz-text-red-800 dz-font-medium dz-dark:text-red-100 mb-0">{{ $error }}</h3>
                </div>
            </div>
        @endif

        <div @click="$refs.input.click()" class="dz-border dz-border-dashed dz-rounded dz-border-gray-500 dz-w-full dz-cursor-pointer">
            <div>
                <div x-show="!isDragging" class="dz-flex dz-items-center dz-bg-gray-50 dz-justify-center dz-gap-3 dz-py-8 dz-h-full dz-dark:bg-gray-700">
                    <div>
                        <p class="dz-text-sm dz-md:text-base dz-text-gray-600 dz-dark:text-gray-400 text-center mb-0">
                            Déposez le fichier ou Cliquez
                            <br>
                            pour l'ajouter depuis votre ordinateur
                        </p>
                    </div>
                </div>
                <div x-show="isDragging" class="dz-flex dz-items-center dz-bg-gray-100 dz-dark:bg-gray-800 dz-justify-center dz-gap-3 dz-py-8 dz-h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="dz-w-4 dz-h-4 dz-md:w-6 dz-md:h-6 dz-text-gray-500 dz-dark:text-gray-400">
                        <path d="M10 2a.75.75 0 01.75.75v5.59l1.95-2.1a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0L6.2 7.26a.75.75 0 111.1-1.02l1.95 2.1V2.75A.75.75 0 0110 2z" />
                        <path d="M5.273 4.5a1.25 1.25 0 00-1.205.918l-1.523 5.52c-.006.02-.01.041-.015.062H6a1 1 0 01.894.553l.448.894a1 1 0 00.894.553h3.438a1 1 0 00.86-.49l.606-1.02A1 1 0 0114 11h3.47a1.318 1.318 0 00-.015-.062l-1.523-5.52a1.25 1.25 0 00-1.205-.918h-.977a.75.75 0 010-1.5h.977a2.75 2.75 0 012.651 2.019l1.523 5.52c.066.239.099.485.099.732V15a2 2 0 01-2 2H3a2 2 0 01-2-2v-3.73c0-.246.033-.492.099-.73l1.523-5.521A2.75 2.75 0 015.273 3h.977a.75.75 0 010 1.5h-.977z" />
                    </svg>
                    <p class="dz-text-sm dz-md:text-base dz-text-gray-600 dz-dark:text-gray-400 mb-0">Déposez le fichier pour l'envoyer</p>
                </div>
            </div>
            <input
                    x-ref="input"
                    type="file"
                    class="dz-hidden"
                    x-on:livewire-upload-start="isLoading = true"
                    x-on:livewire-upload-cancel="isLoading = false"
                    x-on:livewire-upload-finish="isLoading = false"
                    x-on:livewire-upload-error="console.log('livewire-dropzone upload error')"
                    x-on:change.prevent="onChange"
                    @if(! is_null($this->accept)) accept="{{ $this->accept }}" @endif
                    @if($multiple === true) multiple @endif
            >
        </div>

        <div class="dz-flex dz-justify-between dz-w-full dz-mt-2">
            <div class="dz-flex dz-gap-3 dz-text-gray-500 dz-text-xs dz-md:text-sm">
                @php
                    $hasMaxFileSize = ! is_null($this->maxFileSize);
                    $hasMimes = ! empty($this->mimes);
                @endphp

                @if($hasMaxFileSize)
                    <p class="mb-0">{{ __('Max. :size', ['size' => \Illuminate\Support\Number::fileSize($this->maxFileSize * 1024)]) }}</p>
                @endif

                @if($hasMaxFileSize && $hasMimes)
                    <span class="dz-w-1 dz-h-1 dz-text-gray-400">·</span>
                @endif

                @if($hasMimes)
                    <p class="mb-0">{{ Str::upper($this->mimes) }}</p>
                @endif
            </div>
            <div x-show="isLoading" class="dz-flex dz-gap-1 dz-items-center">
                <svg aria-hidden="true" width="15" height="15" class="dz-text-gray-200 dz-animate-spin dz-dark:text-gray-700 dz-fill-gray-800 dz-dark:fill-gray-200" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="dz-sr-only">Chargement...</span> <span x-text="progress"></span> %
                <div @click="cancelUpload" class="dz-text-xs dz-md:text-sm dz-text-gray-800 dz-dark:text-gray-200 dz-hover:cursor-pointer dz-underline">Annuler l'envoi</div>
            </div>
        </div>

        @if(isset($files) && count($files) > 0)
            <div class="dz-flex dz-flex-wrap dz-gap-x-10 dz-gap-y-2 dz-justify-start dz-w-full dz-mt-5">
                @foreach($files as $file)
                    <div class="dz-flex dz-items-center dz-justify-between dz-gap-2 dz-border dz-rounded dz-border-gray-200 dz-w-full dz-h-auto dz-overflow-hidden dz-dark:border-gray-700">
                        <div class="dz-flex dz-items-center dz-gap-3">
                            @if($this->isImageMime($file['extension']))
                                <div class="dz-flex-none dz-w-14 dz-h-14">
                                    <img src="{{ $file['temporaryUrl'] }}" class="dz-object-fill dz-w-full dz-h-full" alt="{{ $file['name'] }}">
                                </div>
                            @else
                                <div class="dz-flex dz-justify-center dz-items-center dz-w-14 dz-h-14 dz-bg-gray-100 dz-dark:bg-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="dz-w-8 dz-h-8 dz-text-gray-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="dz-flex dz-flex-col dz-items-start dz-gap-1">
                                <div class="dz-text-start dz-line-clamp-1 dz-text-slate-900 dz-text-xs dz-md:text-sm dz-font-medium dz-dark:text-slate-100">{{ $file['name'] }}</div>
                                <div class="dz-text-start dz-text-gray-500 dz-text-xs dz-md:text-sm dz-font-medium">{{ \Illuminate\Support\Number::fileSize($file['size']) }}</div>
                            </div>
                        </div>
                        <div class="dz-flex dz-items-center dz-mr-3">
                            <button type="button" @click="removeUpload('{{ $file['tmpFilename'] }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="dz-w-6 dz-h-6 dz-text-black dz-dark:text-white">
                                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @script
    <script>
        Alpine.data('dropzone', ({ _this, uuid }) => {
            return ({
                chunks: {},
                totalChunks: 0,
                uploadedChunks: {},
                isDragging: false,
                isLoading: false,
                isCancelled: false,
                progress: 0,
                onUploadError() {
                    this.chunks = {};
                    this.uploadedChunks = {};
                    this.isLoading = false
                    this.progress = 0

                    if (this.$refs.input) {
                        this.$refs.input.value = ''
                    }
                },
                onChange(e) {
                    this.isCancelled = false
                    this.progress = 0
                    const files = [...e.target.files];

                    files.forEach((file) => {
                        const fileId = Math.random().toString(36).substring(2, 9);
                        this.createChunks(fileId, file);
                    });

                    this.uploadChunks()

                    if (this.$refs.input) {
                        this.$refs.input.value = ''
                    }
                },
                onDrop(e) {
                    this.isCancelled = false
                    this.isDragging = false
                    this.progress = 0

                    const files = [...e.dataTransfer.files]

                    files.forEach((file) => {
                        const fileId = Math.random().toString(36).substring(2, 9);
                        this.createChunks(fileId, file);
                    });

                    this.uploadChunks()

                    if (this.$refs.input) {
                        this.$refs.input.value = ''
                    }
                },
                cancelUpload() {
                    this.isCancelled = true

                    try {
                        _this.cancelUpload('chunk')
                    } catch (e) {
                        // ignore if no upload in progress
                    }

                    this.chunks = {};
                    this.uploadedChunks = {};
                    this.isLoading = false
                    this.progress = 0

                    if (this.$refs.input) {
                        this.$refs.input.value = ''
                    }
                },
                removeUpload(tmpFilename) {
                    // Dispatch an event to remove the temporarily uploaded file
                    _this.dispatch(uuid + ':fileRemoved', { tmpFilename })
                },
                createChunks(fileId, file) {
                    let start = 0;
                    const chunkSize = @js($chunkSize);
                    this.chunks[fileId] = [];

                    // Split file into chunks and add a name property to each blob
                    while (start < file.size) {
                        const end = Math.min(start + chunkSize, file.size);
                        const chunk = file.slice(start, end);
                        const chunkNo = Math.ceil(start / chunkSize) + 1;
                        chunk.name = `${fileId}.${file.name}.${chunkNo}.part`;
                        this.chunks[fileId].push(chunk);
                        start = end;
                    }
                },
                async uploadChunks() {
                    this.isLoading = true
                    this.isCancelled = false

                    const fileIds = Object.keys(this.chunks)

                    for (const fileId of fileIds) {
                        if (this.isCancelled) break;
                        if (this.uploadedChunks[fileId] !== undefined) continue;

                        this.uploadedChunks[fileId] = 0;

                        await this.processFile(fileId)
                    }

                    if (this.isCancelled || Object.keys(this.chunks).length === 0) {
                        this.isLoading = false
                    }
                },
                uploadChunk(fileId, chunk) {
                    return new Promise((resolve, reject) => {
                        const onUploadComplete = () => {
                            if (this.isCancelled) {
                                return resolve('cancelled')
                            }

                            this.uploadedChunks[fileId]++

                            const totalChunks = Object.values(this.chunks).reduce((acc, chunks) => acc + chunks.length, 0);
                            const uploadedChunks = Object.values(this.uploadedChunks).reduce((acc, count) => acc + count, 0);

                            if (totalChunks > 0) {
                                this.progress = Math.round((uploadedChunks / totalChunks) * 100);
                            }
                            resolve()
                        }

                        const onUploadError = (error) => {
                            console.error('livewire-dropzone upload error', error)
                            reject(error)
                        }

                        const onUploading = () => {
                            this.isLoading = true
                        }

                        const args = ['chunk', chunk, onUploadComplete, onUploadError, onUploading]
                        _this.upload(...args)
                    })
                },
                async processFile(fileId) {
                    const fileChunks = this.chunks[fileId] || []

                    for (let i = 0; i < fileChunks.length; i++) {
                        if (this.isCancelled) {
                            try { _this.cancelUpload('chunk') } catch (e) {}
                            break
                        }

                        try {
                            await this.uploadChunk(fileId, fileChunks[i])
                        } catch (e) {
                            this.isLoading = false
                            delete this.chunks[fileId]
                            delete this.uploadedChunks[fileId]
                            return
                        }
                    }

                    if (!this.isCancelled && this.uploadedChunks[fileId] === (this.chunks[fileId]?.length || 0)) {
                        _this.call('mergeChunks', fileId)

                        delete this.chunks[fileId]
                        delete this.uploadedChunks[fileId]
                    }

                    if (Object.keys(this.chunks).length === 0) {
                        this.isLoading = false
                    }
                },
            });
        });
    </script>
    @endscript
</div>
