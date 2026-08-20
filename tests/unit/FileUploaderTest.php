<?php

use App\Libraries\FileUploader;
use App\Libraries\Storage\LocalDriver;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FileUploaderTest extends CIUnitTestCase
{
    public function testUploadUsesConfiguredDriverAndReturnsUrl(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'ci4-upload');
        file_put_contents($tempFile, 'hello world');

        $uploadedFile = new UploadedFile(
            $tempFile,
            filesize($tempFile),
            UPLOAD_ERR_OK,
            'photo.jpg',
            'image/jpeg'
        );

        $driver = new LocalDriver(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ci4-storage' . DIRECTORY_SEPARATOR, 'https://example.com/uploads/');
        $uploader = new FileUploader([], $driver);

        $result = $uploader->upload($uploadedFile, 'avatar');

        $this->assertStringStartsWith('uploads/avatar/' . date('Y') . '/' . date('m') . '/', $result['path']);
        $this->assertStringContainsString('https://example.com/uploads/', $result['url']);
        $this->assertSame('photo.jpg', $result['original']);
        $this->assertSame('jpg', $result['extension']);

        unlink($tempFile);
        $uploader->delete($result['path']);
    }
}
