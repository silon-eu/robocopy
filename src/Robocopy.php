<?php

namespace Silon;

class Robocopy
{
    /* EXAMPLE
    $copy = new \App\Model\Utils\Robocopy();
    $copy->setSourceBasePath('/source/')
        ->setTargetBasePath('\\\\fs\\neco\\')
        ->copy('text.txt');
    */

    protected string $targetBasePath = '';

    protected string $sourceBasePath = '';

    protected ?string $lastCommand = null;

    protected ?array $lastOutput = null;

    protected ?int $lastResultCode = null;

    public function __construct()
    {

    }

    /**
     * @param string|array|string[] $fileNames
     * @param string|null $additionalOptions
     * @return bool
     * @throws RobocopyException
     */
    public function copy(string|array $fileNames, ?string $additionalOptions): bool
    {
        // normalize input to array
        if (is_string($fileNames)) { $fileNames = [$fileNames]; }

        $fNames = '"'.implode('" "',$fileNames).'"';
        $this->lastCommand = "robocopy \"$this->sourceBasePath\" \"$this->targetBasePath\" $fNames $additionalOptions";
        exec($this->lastCommand,$this->lastOutput, $this->lastResultCode);

        if (in_array($this->lastResultCode,[0,1])) {
            return (bool) $this->lastResultCode;
        } else {
            throw new RobocopyException($this->getLastResultMessage(),$this->lastResultCode);
        }
    }

    /**
     * @param string|array $fileNames
     * @param bool $withFolders If false, moves files only, leaving the empty folder structure at the source
     * @return bool
     * @throws RobocopyException
     */
    public function move(string|array $fileNames, bool $withFolders = false): bool
    {
        $options = $withFolders ? '/MOVE' : '/MOV';
        return $this->copy($fileNames,$options);
    }

    /**
     * @return string
     */
    public function getTargetBasePath(): string
    {
        return $this->targetBasePath;
    }

    /**
     * @param string $targetBasePath
     * @return Robocopy
     */
    public function setTargetBasePath(string $targetBasePath): Robocopy
    {
        $this->targetBasePath = rtrim($targetBasePath,'\\/');
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceBasePath(): string
    {
        return $this->sourceBasePath;
    }

    /**
     * @param string $sourceBasePath
     * @return Robocopy
     */
    public function setSourceBasePath(string $sourceBasePath): Robocopy
    {
        $this->sourceBasePath = rtrim($sourceBasePath,'\\/');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastCommand(): ?string
    {
        return $this->lastCommand;
    }

    /**
     * @return array|null
     */
    public function getLastOutput(): ?array
    {
        return $this->lastOutput;
    }

    /**
     * @return int|null
     */
    public function getLastResultCode(): ?int
    {
        return $this->lastResultCode;
    }

    /**
     * @return string
     */
    public function getLastResultMessage(): string
    {
        return match($this->lastResultCode)
        {
            0 => 'No files were copied. No failure was encountered. No files were mismatched. The files already exist in the destination directory; therefore, the copy operation was skipped.',
            1 => 'All files were copied successfully.',
            2 => 'There are some additional files in the destination directory that are not present in the source directory. No files were copied.',
            3 => 'Some files were copied. Additional files were present. No failure was encountered.',
            5 => 'Some files were copied. Some files were mismatched. No failure was encountered.',
            6 => 'Additional files and mismatched files exist. No files were copied and no failures were encountered. This means that the files already exist in the destination directory.',
            7 => 'Files were copied, a file mismatch was present, and additional files were present.',
            default => 'Several files did not copy.'
        };
    }

}