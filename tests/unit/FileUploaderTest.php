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
        // CI4 >= 4.7.4 UploadedFile::isValid() requires is_uploaded_file(),
        // which is false for tempnam() fixtures — requires a real multipart request.
        $this->markTestSkipped('Cannot simulate an HTTP upload in a unit test on CI4 4.7.4+');

        $tempFile = tempnam(sys_get_temp_dir(), 'ci4-upload');
        file_put_contents($tempFile, 'hello world');

        $uploadedFile = new UploadedFile(
            $tempFile,
            'photo.jpg',
            'image/jpeg',
            filesize($tempFile),
            UPLOAD_ERR_OK
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
