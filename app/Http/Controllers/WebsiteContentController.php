<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\HeroSetting;
use App\Models\ContactSetting;
use App\Models\ContactMessage;
use App\Models\Attraction;
use App\Models\AboutSetting;
use App\Models\AboutImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WebsiteContentController extends Controller
{
    // ── Admin page ────────────────────────────────────────────────────────────
    public function index()
    {
        $hero        = HeroSetting::instance();
        $contact     = ContactSetting::instance();
        $attractions = Attraction::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'name'        => $a->name,
                'description' => $a->description,
                'location'    => $a->location,
                'image_url'   => $a->image_url,
                'sort_order'  => $a->sort_order,
            ]);
        $about       = AboutSetting::instance();
        $aboutImages = AboutImage::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn($i) => ['id' => $i->id, 'image_url' => $i->image_url, 'sort_order' => $i->sort_order]);

        $messages    = ContactMessage::latest()->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'name'       => $m->name,
                'email'      => $m->email,
                'phone'      => $m->phone,
                'message'    => $m->message,
                'is_read'    => $m->is_read,
                'created_at' => $m->created_at->format('M j, Y g:i A'),
            ]);
        $unreadCount = ContactMessage::where('is_read', false)->count();

        return Inertia::render('AdminSetWCPage', [
            'hero' => [
                'tagline'              => $hero->tagline,
                'barangay'             => $hero->barangay,
                'mun_prov'             => $hero->mun_prov,
                'sub'                  => $hero->sub,
                'background_image_url' => $hero->background_image_url,
            ],
            'contact' => [
                'email'         => $contact->email,
                'phone'         => $contact->phone,
                'email_hours'   => $contact->email_hours,
                'phone_hours'   => $contact->phone_hours,
                'facebook_url'  => $contact->facebook_url,
                'instagram_url' => $contact->instagram_url,
                'twitter_url'   => $contact->twitter_url,
            ],
            'attractions'  => $attractions,
            'about' => [
                'title'          => $about->title,
                'subtitle'       => $about->subtitle,
                'feature1_title' => $about->feature1_title,
                'feature1_desc'  => $about->feature1_desc,
                'feature2_title' => $about->feature2_title,
                'feature2_desc'  => $about->feature2_desc,
                'feature3_title' => $about->feature3_title,
                'feature3_desc'  => $about->feature3_desc,
            ],
            'about_images' => $aboutImages,
            'messages'     => $messages,
            'unread_count' => $unreadCount,
        ]);
    }

    // ── Hero ──────────────────────────────────────────────────────────────────
    public function updateHero(Request $request)
    {
        $request->validate([
            'tagline'          => 'required|string|max:255',
            'barangay'         => 'required|string|max:255',
            'mun_prov'         => 'nullable|string|max:255',
            'sub'              => 'nullable|string|max:255',
            'background_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $hero   = HeroSetting::instance();
        $before = ['tagline' => $hero->tagline, 'barangay' => $hero->barangay, 'mun_prov' => $hero->mun_prov, 'sub' => $hero->sub];

        if ($request->hasFile('background_image')) {
            if ($hero->background_image) {
                Storage::disk('public')->delete($hero->background_image);
            }
            $hero->background_image = $request->file('background_image')->store('hero', 'public');
        }

        $hero->tagline  = $request->tagline;
        $hero->barangay = $request->barangay;
        $hero->mun_prov = $request->mun_prov;
        $hero->sub      = $request->sub;
        $hero->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'website_content',
            'target_type' => 'HeroSetting',
            'target_id'   => '1',
            'old_values'  => json_encode($before),
            'new_values'  => json_encode(['tagline' => $hero->tagline, 'barangay' => $hero->barangay, 'mun_prov' => $hero->mun_prov, 'sub' => $hero->sub]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Hero section updated successfully!');
    }

    // ── Contact ───────────────────────────────────────────────────────────────
    public function updateContact(Request $request)
    {
        $request->validate([
            'email'         => 'required|email|max:255',
            'phone'         => 'required|string|max:50',
            'email_hours'   => 'nullable|string|max:255',
            'phone_hours'   => 'nullable|string|max:255',
            'facebook_url'  => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url'   => 'nullable|url|max:255',
        ]);

        $contact = ContactSetting::instance();
        $before  = ['email' => $contact->email, 'phone' => $contact->phone];

        $contact->email         = $request->email;
        $contact->phone         = $request->phone;
        $contact->email_hours   = $request->email_hours;
        $contact->phone_hours   = $request->phone_hours;
        $contact->facebook_url  = $request->facebook_url  ?: null;
        $contact->instagram_url = $request->instagram_url ?: null;
        $contact->twitter_url   = $request->twitter_url   ?: null;
        $contact->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'website_content',
            'target_type' => 'ContactSetting',
            'target_id'   => '1',
            'old_values'  => json_encode($before),
            'new_values'  => json_encode(['email' => $contact->email, 'phone' => $contact->phone]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('websitecontent')->with('success', 'Contact info updated successfully!');
    }

    // ── Contact Messages ──────────────────────────────────────────────────────
    public function sendMessage(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        ContactMessage::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return back()->with('contact_success', true);
    }

    public function markMessageRead($id)
    {
        ContactMessage::findOrFail($id)->update(['is_read' => true]);
        return redirect()->back();
    }

    public function deleteMessage($id)
    {
        ContactMessage::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Message deleted.');
    }

    // ── Attractions (CMS) ─────────────────────────────────────────────────────
    public function storeAttraction(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $attraction              = new Attraction();
        $attraction->name        = $request->name;
        $attraction->location    = $request->location;
        $attraction->description = $request->description;
        $attraction->sort_order  = Attraction::max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $attraction->image = $request->file('image')->store('attractions', 'public');
        }

        $attraction->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'website_content',
            'target_type' => 'Attraction',
            'target_id'   => (string) $attraction->id,
            'new_values'  => json_encode(['name' => $attraction->name, 'location' => $attraction->location]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('websitecontent')->with('success', 'Attraction added successfully!');
    }

    public function updateAttraction(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $attraction = Attraction::findOrFail($id);
        $before     = ['name' => $attraction->name, 'description' => $attraction->description, 'location' => $attraction->location];

        $attraction->name        = $request->name;
        $attraction->location    = $request->location;
        $attraction->description = $request->description;

        if ($request->hasFile('image')) {
            if ($attraction->image) {
                Storage::disk('public')->delete($attraction->image);
            }
            $attraction->image = $request->file('image')->store('attractions', 'public');
        }

        $attraction->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'website_content',
            'target_type' => 'Attraction',
            'target_id'   => (string) $id,
            'old_values'  => json_encode($before),
            'new_values'  => json_encode(['name' => $attraction->name, 'location' => $attraction->location]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('websitecontent')->with('success', 'Attraction updated successfully!');
    }

    public function destroyAttraction($id)
    {
        $attraction = Attraction::findOrFail($id);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'deleted',
            'module'      => 'website_content',
            'target_type' => 'Attraction',
            'target_id'   => (string) $id,
            'old_values'  => json_encode(['name' => $attraction->name, 'location' => $attraction->location]),
            'ip_address'  => request()->ip(),
        ]);

        if ($attraction->image) {
            Storage::disk('public')->delete($attraction->image);
        }

        $attraction->delete();

        return redirect()->route('websitecontent')->with('success', 'Attraction deleted successfully!');
    }

    // ── About ─────────────────────────────────────────────────────────────────
    public function updateAbout(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:255',
            'subtitle'       => 'required|string|max:255',
            'feature1_title' => 'required|string|max:255',
            'feature1_desc'  => 'required|string|max:1000',
            'feature2_title' => 'required|string|max:255',
            'feature2_desc'  => 'required|string|max:1000',
            'feature3_title' => 'required|string|max:255',
            'feature3_desc'  => 'required|string|max:1000',
        ]);

        $about  = AboutSetting::instance();
        $before = ['title' => $about->title, 'subtitle' => $about->subtitle];

        $about->title          = $request->title;
        $about->subtitle       = $request->subtitle;
        $about->feature1_title = $request->feature1_title;
        $about->feature1_desc  = $request->feature1_desc;
        $about->feature2_title = $request->feature2_title;
        $about->feature2_desc  = $request->feature2_desc;
        $about->feature3_title = $request->feature3_title;
        $about->feature3_desc  = $request->feature3_desc;
        $about->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'updated',
            'module'      => 'website_content',
            'target_type' => 'AboutSetting',
            'target_id'   => '1',
            'old_values'  => json_encode($before),
            'new_values'  => json_encode(['title' => $about->title, 'subtitle' => $about->subtitle]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('websitecontent')->with('success', 'About section updated successfully!');
    }

    public function storeAboutImage(Request $request)
    {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png|max:5120']);

        $img             = new AboutImage();
        $img->image      = $request->file('image')->store('about', 'public');
        $img->sort_order = AboutImage::max('sort_order') + 1;
        $img->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'created',
            'module'      => 'website_content',
            'target_type' => 'AboutImage',
            'target_id'   => (string) $img->id,
            'new_values'  => json_encode(['image' => $img->image]),
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('websitecontent')->with('success', 'Image added successfully!');
    }

    public function destroyAboutImage($id)
    {
        $img = AboutImage::findOrFail($id);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'deleted',
            'module'      => 'website_content',
            'target_type' => 'AboutImage',
            'target_id'   => (string) $id,
            'old_values'  => json_encode(['image' => $img->image]),
            'ip_address'  => request()->ip(),
        ]);

        if ($img->image) {
            Storage::disk('public')->delete($img->image);
        }

        $img->delete();

        return redirect()->route('websitecontent')->with('success', 'Image removed successfully!');
    }

    // ── Public Landing Page ───────────────────────────────────────────────────
    public function landingPage()
    {
        $hero        = HeroSetting::instance();
        $contact     = ContactSetting::instance();
        $about       = AboutSetting::instance();
        $attractions = Attraction::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'name'        => $a->name,
                'location'    => $a->location,
                'description' => $a->description,
                'image_url'   => $a->image_url,
            ]);
        $aboutImages = AboutImage::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn($i) => ['id' => $i->id, 'image_url' => $i->image_url]);

        return Inertia::render('LandingPage', [
            'hero' => [
                'tagline'              => $hero->tagline,
                'barangay'             => $hero->barangay,
                'mun_prov'             => $hero->mun_prov,
                'sub'                  => $hero->sub,
                'background_image_url' => $hero->background_image_url,
            ],
            'contact' => [
                'email'         => $contact->email,
                'phone'         => $contact->phone,
                'email_hours'   => $contact->email_hours,
                'phone_hours'   => $contact->phone_hours,
                'facebook_url'  => $contact->facebook_url,
                'instagram_url' => $contact->instagram_url,
                'twitter_url'   => $contact->twitter_url,
            ],
            'attractions' => $attractions,
            'about' => [
                'title'          => $about->title,
                'subtitle'       => $about->subtitle,
                'feature1_title' => $about->feature1_title,
                'feature1_desc'  => $about->feature1_desc,
                'feature2_title' => $about->feature2_title,
                'feature2_desc'  => $about->feature2_desc,
                'feature3_title' => $about->feature3_title,
                'feature3_desc'  => $about->feature3_desc,
            ],
            'about_images' => $aboutImages,
        ]);
    }
}