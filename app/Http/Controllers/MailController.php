<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail; 
use App\Mail\MailModel;

class MailController extends Controller
{
    public function send(Request $request)
    {
        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('auctions.home')->with('success', 'Email sent successfully!');
    }

    public function sendTestEmail()
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');
    
        if (!$fromAddress || !$fromName) {
            return response()->json([
                'message' => 'As variáveis MAIL_FROM_ADDRESS ou MAIL_FROM_NAME não estão configuradas.',
                'address' => $fromAddress,
                'name' => $fromName,
            ], 500);
        }
    
        $mailData = [
            'name' => 'John Doe',
            'email' => 'recipient@example.com',
        ];
    
        Mail::to($mailData['email'])->send(new MailModel($mailData));
    
        return response()->json(['message' => 'Test email sent successfully!']);
    }    

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:member,email',
        ]);

        // Find the user by email
        $user = Member::where('email', $request->email)->first();

        // Generate a random password
        $randomPassword = $this->generateRandomPassword();

        // Update the user's password
        $user->password = bcrypt($randomPassword);
        $user->save();

        // Prepare the email data
        $mailData = [
            'name' => $user->username, // Use the username
            'email' => $user->email,
            'password' => $randomPassword,
        ];

        // Send the email
        Mail::to($user->email)->send(new MailModel($mailData));

        return redirect()->back()->with('success', 'A new password has been sent to your email.');
    }

    private function generateRandomPassword()
    {
        $length = rand(8, 12); // Random length between 8 and 12
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        return substr(str_shuffle($characters), 0, $length);
    }

}
