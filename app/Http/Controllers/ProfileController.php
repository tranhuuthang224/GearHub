<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile view with tabs: Profile, Addresses, Community Posts.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        $myPosts = $user->communityPosts()
            ->withCount(['products', 'comments', 'likes'])
            ->latest('id')
            ->paginate(6, ['*'], 'posts_page');

        $activeTab = $request->query('tab', session('active_tab', 'profile'));

        return view('profile.edit', [
            'user'       => $user,
            'addresses'  => $addresses,
            'myPosts'    => $myPosts,
            'activeTab'  => $activeTab,
        ]);
    }

    /**
     * Update the user's profile information (name, phone, email, avatar).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;

        // Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if stored locally
            if ($user->avatar && !str_starts_with($user->avatar, 'http://') && !str_starts_with($user->avatar, 'https://')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('active_tab', 'profile')
            ->with('success', 'Cập nhật thông tin cá nhân thành công!');
    }

    /**
     * Add a new shipping address for the user.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone'         => ['required', 'string', 'regex:/^(0|\+84)[35789][0-9]{8}$/'],
            'address_line'  => ['required', 'string', 'min:10', 'max:255'],
            'is_default'    => ['nullable', 'boolean'],
        ], [
            'receiver_name.required' => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'         => 'Vui lòng nhập số điện thoại người nhận.',
            'phone.regex'            => 'Số điện thoại không đúng định dạng Việt Nam.',
            'address_line.required'  => 'Vui lòng nhập địa chỉ nhận hàng chi tiết.',
            'address_line.min'       => 'Địa chỉ nhận hàng tối thiểu 10 ký tự.',
        ]);

        $user = $request->user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'receiver_name' => $validated['receiver_name'],
            'phone'         => $validated['phone'],
            'address_line'  => $validated['address_line'],
            'is_default'    => $isDefault,
        ]);

        return Redirect::route('profile.edit', ['tab' => 'addresses'])
            ->with('active_tab', 'addresses')
            ->with('success', 'Đã thêm địa chỉ nhận hàng mới thành công!');
    }

    /**
     * Update an existing shipping address.
     */
    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa địa chỉ này.');
        }

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'min:2', 'max:100'],
            'phone'         => ['required', 'string', 'regex:/^(0|\+84)[35789][0-9]{8}$/'],
            'address_line'  => ['required', 'string', 'min:10', 'max:255'],
            'is_default'    => ['nullable', 'boolean'],
        ], [
            'receiver_name.required' => 'Vui lòng nhập họ tên người nhận.',
            'phone.required'         => 'Vui lòng nhập số điện thoại người nhận.',
            'phone.regex'            => 'Số điện thoại không đúng định dạng Việt Nam.',
            'address_line.required'  => 'Vui lòng nhập địa chỉ nhận hàng chi tiết.',
            'address_line.min'       => 'Địa chỉ nhận hàng tối thiểu 10 ký tự.',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'receiver_name' => $validated['receiver_name'],
            'phone'         => $validated['phone'],
            'address_line'  => $validated['address_line'],
            'is_default'    => $isDefault || $address->is_default,
        ]);

        return Redirect::route('profile.edit', ['tab' => 'addresses'])
            ->with('active_tab', 'addresses')
            ->with('success', 'Đã cập nhật thông tin địa chỉ thành công!');
    }

    /**
     * Delete an address from the user's address book.
     */
    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền xóa địa chỉ này.');
        }

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->first()?->update(['is_default' => true]);
        }

        return Redirect::route('profile.edit', ['tab' => 'addresses'])
            ->with('active_tab', 'addresses')
            ->with('success', 'Đã xóa địa chỉ nhận hàng thành công!');
    }

    /**
     * Set an address as the default shipping address.
     */
    public function setDefaultAddress(Request $request, Address $address): RedirectResponse
    {
        if ($address->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền thao tác trên địa chỉ này.');
        }

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return Redirect::route('profile.edit', ['tab' => 'addresses'])
            ->with('active_tab', 'addresses')
            ->with('success', 'Đã đặt địa chỉ làm mặc định!');
    }

    /**
     * Delete a community post authored by the user.
     */
    public function destroyPost(Request $request, CommunityPost $post): RedirectResponse
    {
        if ($post->user_id !== $request->user()->id) {
            abort(403, 'Bạn không có quyền xóa bài viết này.');
        }

        if ($post->image && !str_starts_with($post->image, 'http://') && !str_starts_with($post->image, 'https://')) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return Redirect::route('profile.edit', ['tab' => 'posts'])
            ->with('active_tab', 'posts')
            ->with('success', 'Đã xóa bài viết góc máy thành công!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
