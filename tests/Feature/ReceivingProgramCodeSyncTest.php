<?php

use Illuminate\Support\Facades\File;

it('wires program input changes to product code refresh logic on receiving create and edit views', function () {
    $createView = File::get(resource_path('views/receivings/create.blade.php'));
    $editView = File::get(resource_path('views/receivings/edit.blade.php'));

    expect($createView)->toContain('bindProgramCodeSync');
    expect($editView)->toContain('bindProgramCodeSync');
});
