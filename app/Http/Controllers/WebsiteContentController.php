<?php

namespace App\Http\Controllers;

use App\Models\HeroSetting;
use App\Models\ContactSetting;
use App\Models\Attraction;
use Illuminate\Http\Request;
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
                'image_url'   => $a->image_url,
                'sort_order'  => $a->sort_order,
            ]);

        return Inertia::render('AdminSetWCPage', [
            'hero' => [
                'tagline'              => $hero->tagline,
                'barangay'             => $hero->barangay,
                'mun_prov'             => $hero->mun_prov,
                'sub'                  => $hero->sub,
                'background_image_url' => $hero->background_image_url,
            ],
            'contact' => [
                'email'       => $contact->email,
                'phone'       => $contact->phone,
                'email_hours' => $contact->email_hours,
                'phone_hours' => $contact->phone_hours,
            ],
            'attractions' => $attractions,
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

        $hero = HeroSetting::instance();

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

        return redirect()->back()->with('success', 'Hero section updated successfully!');
    }

    // ── Contact ───────────────────────────────────────────────────────────────

    public function updateContact(Request $request)
    {
        $request->validate([
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:50',
            'email_hours' => 'nullable|string|max:255',
            'phone_hours' => 'nullable|string|max:255',
        ]);

        $contact              = ContactSetting::instance();
        $contact->email       = $request->email;
        $contact->phone       = $request->phone;
        $contact->email_hours = $request->email_hours;
        $contact->phone_hours = $request->phone_hours;
        $contact->save();

        return redirect()->back()->with('success', 'Contact info updated successfully!');
    }

    // ── Attractions ───────────────────────────────────────────────────────────

    public function storeAttraction(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $attraction              = new Attraction();
        $attraction->name        = $request->name;
        $attraction->description = $request->description;
        $attraction->sort_order  = Attraction::max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $attraction->image = $request->file('image')->store('attractions', 'public');
        }

        $attraction->save();

        return redirect()->back()->with('success', 'Attraction added successfully!');
    }

    public function updateAttraction(Request $request, $id)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $attraction              = Attraction::findOrFail($id);
        $attraction->name        = $request->name;
        $attraction->description = $request->description;

        if ($request->hasFile('image')) {
            if ($attraction->image) {
                Storage::disk('public')->delete($attraction->image);
            }
            $attraction->image = $request->file('image')->store('attractions', 'public');
        }

        $attraction->save();

        return redirect()->back()->with('success', 'Attraction updated successfully!');
    }

    public function destroyAttraction($id)
    {
        $attraction = Attraction::findOrFail($id);

        if ($attraction->image) {
            Storage::disk('public')->delete($attraction->image);
        }

        $attraction->delete();

        return redirect()->back()->with('success', 'Attraction deleted successfully!');
    }

    // ── Public Landing Page ───────────────────────────────────────────────────

    public function landingPage()
    {
        $hero        = HeroSetting::instance();
        $contact     = ContactSetting::instance();
        $attractions = Attraction::orderBy('sort_order')->orderBy('id')->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'name'        => $a->name,
                'description' => $a->description,
                'image_url'   => $a->image_url,
            ]);

        return Inertia::render('LandingPage', [
            'hero' => [
                'tagline'              => $hero->tagline,
                'barangay'             => $hero->barangay,
                'mun_prov'             => $hero->mun_prov,
                'sub'                  => $hero->sub,
                'background_image_url' => $hero->background_image_url,
            ],
            'contact' => [
                'email'       => $contact->email,
                'phone'       => $contact->phone,
                'email_hours' => $contact->email_hours,
                'phone_hours' => $contact->phone_hours,
            ],
            'attractions' => $attractions,
        ]);
    }
}