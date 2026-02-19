<?php

namespace Dasundev\LivewireDropzone\Http\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Dropzone extends Component
{
    use WithFileUploads;

    #[Modelable]
    public ?array $files;

    #[Locked]
    public array $rules;

    #[Locked]
    public string $uuid;

    public string $error;

    public bool $multiple;

    public $chunk;

    public $chunks = [];

    public $file;

    public function rules(): array
    {
        return [
            'file' => [...$this->rules],
        ];
    }

    public function mount(array $rules = [], bool $multiple = false): void
    {
        $this->uuid = Str::uuid();
        $this->multiple = $multiple;
        $this->rules = $rules;
        $this->files = [];
    }

    /**
     * Called after updating the chunk property.
     */
    public function updatedChunk($value): void
    {
        $fileId = request()->header('X-File-Id');

        if ($fileId) {
            $this->chunks[$fileId][] = $value;
        } else {
            $this->chunks[] = $value;
        }
    }

    /**
     * Merge uploaded file chunks into a single file.
     *
     * @throws \Livewire\Features\SupportFileUploads\FileNotPreviewableException
     */
    public function mergeChunks(string $fileId = null): void
    {
        $disk = FileUploadConfiguration::disk();

        $chunks = $fileId && isset($this->chunks[$fileId]) 
            ? $this->chunks[$fileId] 
            : $this->chunks;

        if (empty($chunks)) {
            return; // Rien à merger
        }

        // 1) Déduire le nom original (sans ".N.part") à partir du premier chunk
        $originalName = preg_replace('/\.\d+\.part$/', '', $chunks[0]->getClientOriginalName());

        // 2) Générer un nom Livewire (même logique que votre code) pour le fichier final
        $finalBasename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded(
            UploadedFile::fake()->create($originalName)
        );

        $relativeDir = trim(FileUploadConfiguration::path(), '/');
        $relativePath = $relativeDir . '/' . $finalBasename; // p.ex. livewire-tmp/XYZ…
        $absolutePath = Storage::disk($disk)->path($relativePath);

        // S’assurer que le dossier existe
        if (! File::exists(dirname($absolutePath))) {
            File::makeDirectory(dirname($absolutePath), 0755, true);
        }

        // 3) Trier les chunks par numéro pour garantir l’ordre
        usort($chunks, function ($a, $b) {
            $na = (int) (preg_match('/\.(\d+)\.part$/', $a->getClientOriginalName(), $ma) ? $ma[1] : 0);
            $nb = (int) (preg_match('/\.(\d+)\.part$/', $b->getClientOriginalName(), $mb) ? $mb[1] : 0);
            return $na <=> $nb;
        });

        // 4) (Re)créer le fichier final et y concaténer chaque chunk
        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }

        foreach ($chunks as $chunk) {
            // Lire le contenu binaire du chunk et l’ajouter au fichier final
            file_put_contents($absolutePath, file_get_contents($chunk->getRealPath()), FILE_APPEND);
        }

        // 5) Recréer l’instance temporaire Livewire sur le fichier final
        $this->file = TemporaryUploadedFile::createFromLivewire(File::basename($relativePath));

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->dispatch("{$this->uuid}:uploadError", $e->getMessage());
            return;
        }

        $this->dispatchTempFileAddedEvent($this->file);

        // Nettoyage d’état
        if ($fileId) {
            unset($this->chunks[$fileId]);
        } else {
            $this->reset('chunks');
        }

        $this->reset('error');
    }

    /**
     * Dispatch an event with the details of the uploaded temporary file.
     *
     * @throws \Livewire\Features\SupportFileUploads\FileNotPreviewableException
     */
    public function dispatchTempFileAddedEvent(TemporaryUploadedFile $file): void
    {
        $this->dispatch("{$this->uuid}:fileAdded", [
            'tmpFilename' => $file->getFilename(),
            'name' => $file->getClientOriginalName(),
            'extension' => $file->extension(),
            'path' => $file->path(),
            'temporaryUrl' => $file->isPreviewable() ? $file->temporaryUrl() : null,
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Handle the file added event.
     */
    #[On('{uuid}:fileAdded')]
    public function onFileAdded(array $file): void
    {
        $this->files = $this->multiple ? array_merge($this->files, [$file]) : [$file];
    }

    /**
     * Handle the file removal event.
     */
    #[On('{uuid}:fileRemoved')]
    public function onFileRemoved(string $tmpFilename): void
    {
        $this->files = array_filter($this->files, function ($file) use ($tmpFilename) {
            // Remove the temporary file from the array only.
            // No need to remove from the Livewire's temporary upload directory manually.
            // Because, files older than 24 hours cleanup automatically by Livewire.
            // For more details, refer to: https://livewire.laravel.com/docs/uploads#configuring-automatic-file-cleanup
            return $file['tmpFilename'] !== $tmpFilename;
        });
    }

    /**
     * Handle the upload error event.
     */
    #[On('{uuid}:uploadError')]
    public function onUploadError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * Retrieve the MIME types from the rules.
     */
    #[Computed]
    public function mimes(): string
    {
        return collect($this->rules)
            ->filter(fn ($rule) => str_starts_with($rule, 'mimes:'))
            ->flatMap(fn ($rule) => explode(',', substr($rule, strpos($rule, ':') + 1)))
            ->unique()
            ->values()
            ->join(', ');
    }

    /**
     * Get the accepted file extensions based on MIME types.
     */
    #[Computed]
    public function accept(): ?string
    {
        return ! empty($this->mimes) ? collect(explode(', ', $this->mimes))->map(fn ($mime) => '.'.$mime)->implode(',') : null;
    }

    /**
     * Get the maximum file size in a human-readable format.
     */
    #[Computed]
    public function maxFileSize(): ?string
    {
        return collect($this->rules)
            ->filter(fn ($rule) => str_starts_with($rule, 'max:'))
            ->flatMap(fn ($rule) => explode(',', substr($rule, strpos($rule, ':') + 1)))
            ->unique()
            ->values()
            ->first();
    }

    /**
     * Checks if the provided MIME type corresponds to an image.
     */
    #[Computed]
    public function isImageMime($mime): bool
    {
        return in_array($mime, ['png', 'gif', 'bmp', 'svg', 'jpeg', 'jpg']);
    }

    public function render(): View
    {
        return view('livewire-dropzone::livewire.dropzone', [
            'chunkSize' => config('livewire-dropzone.chunk_size'),
        ]);
    }
}
