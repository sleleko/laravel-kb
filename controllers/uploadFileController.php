<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function index()
    {
        return view('file-upload');
    }

    public function upload(Request $request)
    {
        // Проверяем, был ли файл отправлен
        if ($request->hasFile('file')) {
            // Получаем файл
            $file = $request->file('file');

            // Проверяем, является ли файл допустимым типом
            if ($file->isValid()) {
                // Получаем имя файла
                $name = $file->getClientOriginalName();

                // Загружаем файл в директорию storage/app/files
                $file->storeAs('files', $name);

                // Возвращаем сообщение об успешной загрузке файла
                return back()->with('success', 'Файл был успешно загружен.');
            }
        }

        // Возвращаем сообщение об ошибке, если файл не был отправлен или не является допустимым типом
        return back()->with('error', 'Произошла ошибка при загрузке файла.');
    }
}



