<?php

namespace InertiaResource\FormFields;

class FileUploadField extends GenericField
{
    protected ?int $maxSize = null;

    protected array $acceptedFileTypes = [];

    protected bool $multiple = false;

    protected ?int $maxFiles = null;

    protected bool $image = false;

    protected bool $imagePreview = true;

    protected ?string $directory = null;

    protected ?string $disk = null;

    protected ?string $visibility = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'file_upload';
    }

    public function maxSize(int $kilobytes): self
    {
        $this->maxSize = $kilobytes;

        return $this;
    }

    public function acceptedFileTypes(array $types): self
    {
        $this->acceptedFileTypes = $types;

        return $this;
    }

    public function multiple(bool $multiple = true): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function maxFiles(int $count): self
    {
        $this->maxFiles = $count;

        return $this;
    }

    public function image(): self
    {
        $this->image = true;
        $this->acceptedFileTypes = ['image/*'];

        return $this;
    }

    public function imagePreview(bool $preview = true): self
    {
        $this->imagePreview = $preview;

        return $this;
    }

    public function directory(string $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    public function disk(string $disk): self
    {
        $this->disk = $disk;

        return $this;
    }

    public function visibility(string $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['maxSize'] = $this->maxSize;
        $array['acceptedFileTypes'] = $this->acceptedFileTypes;
        $array['multiple'] = $this->multiple;
        $array['maxFiles'] = $this->maxFiles;
        $array['image'] = $this->image;
        $array['imagePreview'] = $this->imagePreview;
        $array['directory'] = $this->directory;
        $array['disk'] = $this->disk;
        $array['visibility'] = $this->visibility;

        return $array;
    }
}

