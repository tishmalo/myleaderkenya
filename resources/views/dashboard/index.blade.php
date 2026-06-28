@extends('layouts.app')

@section('page_title', 'Tags & Topics')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-10">
        <div>
            <h1 class="text-3xl font-semibold text-white flex items-center gap-3">
                <i class="fas fa-tags text-emerald-500"></i>
                Discussion Tags & Topics
            </h1>
            <p class="text-zinc-400 mt-1">Manage tags used in messages, discussions, and blogs</p>
        </div>
        
        <button onclick="showCreateTagModal()" 
                class="bg-emerald-600 hover:bg-emerald-700 px-6 py-3 rounded-2xl text-sm font-medium flex items-center gap-2 transition-all">
            <i class="fas fa-plus"></i> 
            Add New Tag
        </button>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($tags as $tag)
                    <div class="group bg-zinc-800 hover:bg-zinc-700 border border-transparent hover:border-emerald-500 rounded-2xl p-6 transition-all duration-300">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-emerald-500/10 rounded-2xl flex items-center justify-center text-3xl text-emerald-400 group-hover:scale-110 transition-transform">
                                #
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl font-semibold text-white truncate">{{ $tag->name }}</h3>
                                <p class="text-emerald-400 text-sm font-mono mt-1">#{{ $tag->slug }}</p>
                            </div>
                        </div>
                        
                        @if($tag->description)
                            <p class="text-zinc-400 text-sm mt-5 line-clamp-3">{{ $tag->description }}</p>
                        @else
                            <p class="text-zinc-500 text-sm mt-5 italic">No description provided.</p>
                        @endif

                        <div class="mt-6 pt-4 border-t border-zinc-700 flex justify-end">
                            <button onclick="deleteTag({{ $tag->id }}, '{{ $tag->name }}')" 
                                    class="text-red-400 hover:text-red-500 text-sm flex items-center gap-1">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 text-zinc-500">
                        No tags found. Create your first tag above.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add New Tag Modal -->
<div id="createTagModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-[9999]">
    <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-md mx-4 p-8">
        <h3 class="text-2xl font-semibold mb-6">Add New Tag</h3>
        
        <form id="createTagForm" onsubmit="submitCreateTag(event)">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Tag Name</label>
                    <input type="text" id="tag_name" 
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                           placeholder="e.g. Healthcare" required>
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Slug</label>
                    <input type="text" id="tag_slug" readonly
                           class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-zinc-500">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Description (optional)</label>
                    <textarea id="tag_description" rows="3"
                              class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white focus:outline-none focus:border-emerald-500"
                              placeholder="Brief description of this topic..."></textarea>
                </div>
            </div>

            <div class="flex gap-4 mt-8">
                <button type="button" onclick="hideCreateTagModal()" 
                        class="flex-1 py-4 border border-zinc-700 rounded-2xl font-medium hover:bg-zinc-800">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-medium">
                    Create Tag
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showCreateTagModal() {
    document.getElementById('createTagModal').classList.remove('hidden');
    document.getElementById('createTagModal').classList.add('flex');
    document.getElementById('tag_name').focus();
}

function hideCreateTagModal() {
    document.getElementById('createTagModal').classList.add('hidden');
    document.getElementById('createTagModal').classList.remove('flex');
}

// Auto generate slug
document.getElementById('tag_name').addEventListener('input', function() {
    let name = this.value.trim();
    let slug = name.toLowerCase()
                   .replace(/[^a-z0-9\s-]/g, '')
                   .replace(/\s+/g, '-')
                   .replace(/-+/g, '-')
                   .replace(/^-|-$/g, '');
    document.getElementById('tag_slug').value = slug;
});

async function submitCreateTag(e) {
    e.preventDefault();
    
    const name = document.getElementById('tag_name').value.trim();
    const slug = document.getElementById('tag_slug').value.trim();
    const description = document.getElementById('tag_description').value.trim();

    if (!name || !slug) {
        alert("Tag name and slug are required");
        return;
    }

    try {
        const response = await fetch('{{ route("tags.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: name,
                slug: slug,
                description: description
            })
        });

        const data = await response.json();

        if (response.ok) {
            hideCreateTagModal();
            location.reload(); // Refresh to show new tag
        } else {
            alert(data.message || "Failed to create tag");
        }
    } catch (error) {
        alert("An error occurred while creating the tag");
    }
}

// Delete Tag - Fixed version
// Improved Delete Tag
async function deleteTag(id, name) {
    if (!confirm(`Are you sure you want to permanently delete the tag "${name}"?`)) {
        return;
    }

    try {
        const response = await fetch(`/tags/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            alert(data.message || "Tag deleted successfully");
            location.reload(); // Refresh the list
        } else if (response.status === 404) {
            alert("Tag not found. It may have already been deleted.");
            location.reload();
        } else {
            const data = await response.json();
            alert(data.message || "Failed to delete tag");
        }
    } catch (error) {
        console.error(error);
        alert("An error occurred while deleting the tag. Please try again.");
        location.reload();
    }
}
// Delete Tag
// async function deleteTag(id, name) {
//     if (!confirm(`Delete tag "${name}"?`)) return;

//     try {
//         const response = await fetch(`/tags/${id}`, {
//             method: 'DELETE',
//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//             }
//         });

//         const data = await response.json();

//         if (response.ok) {
//             location.reload();
//         } else {
//             alert(data.message || "Failed to delete tag");
//         }
//     } catch (error) {
//         alert("An error occurred while deleting the tag");
//     }
// }
</script>
@endpush
