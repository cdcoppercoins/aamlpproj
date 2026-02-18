<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GalleryController extends Controller
{
    private $folderMap = [
        '1936 Goudey Cards'     => 'c36g',
        '1937 Goudey Cards'     => 'c37g',
        '1938 Goudey Cards'     => 'c38g',
        '1939 Goudey Cards'     => 'c39g',
        '1939 Worldwide Gum Cards'     => 'c39w',
        '1939 Globe Trotters'   => 'm39g',
        '1949 Topps Cards'      => 'c49t',
        '1950 Topps Cards'      => 'c50t',
        '1952 Licade Wrappers'  => 'c52m',
        '1953 Gen. Mills (Wheaties)'         => 'm53p',
        '1954 Gen. Mills (Wheaties)'         => 'm54p',
        '1953-54 Cracker Jack cards'    => 'c53c',
        '1954 Canada Quaker Cereal'  => 'm54q',
        '1955 Leader Candy'     => 'm55l',
        '1959 Bakers Chocolate' => 'm59p',
        '1960 Post'             => 'm60p',
        '1961 Topps Stickers'   => 's61t',
        '1963 General Mills'    => 'm63p',
        '1963 Canada GM Stickers'   => 's63g',
        '1966 Canada GM Stickers'   => 's66g',
        '1968 Maple Leaf'       => 'm68m',
        '1968 Quaker Cereal'    => 'm68q',
        '1968 Post Cereal Plates'             => 'm68p',
        '1970 Post Cereal Plates'             => 'm70p',
        '1975 Post Cereal Plates'             => 'm75p',
        '1978 Post Cereal Plates'             => 'm78p',
        '1978 Super Sips candy' => 's78s',
        '1979 Post Cereal Plates'             => 'm79p',
        '1980 Post Cereal Plates'             => 'm80p',
        '1981 Post Cereal Plates'             => 'm81p',
        '1982 Post Cereal Plates'             => 'm82p',
        '1983 Post Cereal Plates'             => 'm83p',
        '1984 Post Cereal Plates'             => 'm84p',
        '1986 Post Cereal Plates'             => 'm86p',
        '1987 Post Cereal Plates'             => 'm87p',
        '1988 Post Cereal Plates'             => 'm88p',
        '1989 Post Cereal Plates'             => 'm89p',
        '1990 Post Cereal Plates'             => 'm90p',
    ];

    public function index()
    {
        $availableSets = [];
        $setThumbnails = [];

        foreach ($this->folderMap as $setName => $folder) {
            $dirPath = public_path('plates/' . $folder);
            $availableSets[$setName] = false;
            $setThumbnails[$setName] = null;

            if (is_dir($dirPath)) {
                $files = scandir($dirPath);
                foreach ($files as $file) {
                    if (preg_match('/a\.(jpg|jpeg|png|gif|webp|bmp)$/i', $file)) {
                        $availableSets[$setName] = true;
                        $setThumbnails[$setName] = asset('plates/' . $folder . '/' . $file);
                        break;
                    }
                }
            }
        }

        return view('gallery', [
            'folderMap' => $this->folderMap,
            'availableSets' => $availableSets,
            'setThumbnails' => $setThumbnails,
        ]);
    }

    public function show(Request $request, $setName)
    {
        $setName = urldecode($setName);
        
        if (!isset($this->folderMap[$setName])) {
            return redirect()->route('gallery');
        }

        $folder = $this->folderMap[$setName];
        $dirPath = public_path('plates/' . $folder);
        $images = [];

        if (is_dir($dirPath)) {
            $files = scandir($dirPath);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;

                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $basename = pathinfo($file, PATHINFO_FILENAME);

                if (preg_match('/_a$/i', $basename) && in_array($ext, $allowedExtensions)) {
                    $baseNoLetter = substr($basename, 0, -1);
                    $aFile = asset('plates/' . $folder . '/' . $file);
                    $bFile = asset('plates/' . $folder . '/' . $baseNoLetter . 'b.' . $ext);

                    if (file_exists($dirPath . '/' . $baseNoLetter . 'b.' . $ext)) {
                        $images[] = ['a' => $aFile, 'b' => $bFile];
                    } else {
                        $images[] = ['a' => $aFile, 'b' => null];
                    }
                }
            }
        }

        // Check for setinfo files
        $infoPath = resource_path('views/setinfo/' . $folder . '_info.blade.php');
        $varPath = resource_path('views/setinfo/' . $folder . '_varieties.blade.php');

        return view('gallery.show', [
            'selectedSet' => $setName,
            'folder' => $folder,
            'images' => $images,
            'hasInfo' => file_exists($infoPath),
            'hasVarieties' => file_exists($varPath),
        ]);
    }
}
