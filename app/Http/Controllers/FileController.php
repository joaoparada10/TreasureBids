<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Member;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class FileController extends Controller
{
    static $default = 'no_image.png';
    static $diskName = 'lbaw24114'; 

    static $systemTypes = [
        'profile_type' => ['png', 'jpg', 'jpeg', 'gif', 'webp'],
        'auction_type' => ['png', 'jpg', 'jpeg', 'webp'],
    ];

    private static function getDefaultExtension(String $type) {
        return reset(self::$systemTypes[$type]);
    }

    private static function isValidExtension(String $type, String $extension) {
        $allowedExtensions = self::$systemTypes[$type];

        return in_array(strtolower($extension), $allowedExtensions);
    }

    private static function isValidType(String $type) {
        return array_key_exists($type, self::$systemTypes);
    }

    private static function defaultAsset(String $type) {
        return asset($type . '/' . self::$default);
    }

    private static function getFileName(String $type, int $id, String $extension = null) {

        $fileName = null;
        switch($type) {
            case 'profile_type':
                $member = Member::find($id);
            $fileName = $member ? $member->profile_pic : null;
            break;
                break;
            case 'auction_type':
                $auction = Auction::find($id);
            $fileName = $auction ? $auction->picture : null;
            break;
            default:
                return null;
        }

        return $fileName;
    }

    private static function delete(String $type, int $id) {
        $existingFileName = self::getFileName($type, $id);
        if ($existingFileName) {
            Storage::disk(self::$diskName)->delete($type . '/' . $existingFileName);

            switch($type) {
                case 'profile_type':
                    Member::find($id)->profile_pic = null;
                    break;
                case 'auction_type':
                    Auction::find($id)->picture = null;
                    break;
            }
        }
    }

    static function upload(Request $request) {

        // Validation: has file
        if (!$request->hasFile('picture')) {
            return redirect()->back()->with('error', 'Error: File not found');
        }

        // Validation: upload type
        if (!self::isValidType($request->type)) {
            return redirect()->back()->with('error', 'Error: Unsupported upload type');
        }

        // Validation: upload extension
        $file = $request->file('picture');
        $type = $request->type;
        $extension = $file->extension();
        if (!self::isValidExtension($type, $extension)) {
            return redirect()->back()->with('error', 'Error: Unsupported upload extension');
        }
        // Prevent existing old files
        if ($request->id){
            self::delete($type, $request->id);
        }

        // Generate unique filename
        $fileName = $file->hashName();

        // Validation: model
        $error = null;
        switch($request->type) {
            case 'profile_type':
                $user = Member::findOrFail($request->id);
                if ($user) {
                    $user->profile_pic = $fileName;
                    $user->save();
                } else {
                    $error = "unknown user";
                }
                break;

            case 'auction_type':
                $auction = Auction::findOrFail($request->id);
                if ($auction) {
                    $auction->picture = $fileName;
                    $auction->save();
                } else {
                    $error = "unknown auction";
                }
                break;

            default:
                return redirect()->back()->with('error', 'Error: Unsupported upload object');
        }

        if ($error) {
            return redirect()->back()->with('error', "Error: {$error}");
        }

        $file->storeAs($type, $fileName, self::$diskName);
        return $fileName;
    }

    static function get(String $type, int $userId) {

        // Validation: upload type
        if (!self::isValidType($type)) {
            return self::defaultAsset($type);
        }

        // Validation: file exists
        $fileName = self::getFileName($type, $userId);
        if ($fileName && Storage::disk(self::$diskName)->exists("$type/$fileName")) {
            return asset("$type/$fileName");
        } else {
            return self::defaultAsset($type);
        }
        
        return self::defaultAsset($type);
    }


}
