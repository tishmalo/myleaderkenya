@extends('layouts.app')

@section('page_title', 'Add New Candidate')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold text-white">Add New Candidate</h1>
        <a href="{{ route('candidates.index') }}" class="text-zinc-400 hover:text-white">← Back</a>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8">
        <form action="{{ route('candidates.store') }}" method="POST" enctype="multipart/form-data" id="candidateForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Nick Name</label>
                    <input type="text" name="nick_name" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Phone</label>
                    <input type="tel" name="phone" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
                <div>
                    <label class="block text-sm text-zinc-400 mb-2">Email</label>
                    <input type="email" name="email" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                </div>
            </div>

            <!-- Political Party -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Political Party</label>
                <select name="political_party_id" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Political Party (Optional)</option>
                    @foreach($politicalParties as $party)
                        <option value="{{ $party->id }}" {{ old('political_party_id') == $party->id ? 'selected' : '' }}>{{ $party->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Position -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Position <span class="text-red-500">*</span></label>
                <select name="position_id" id="positionSelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Position</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dynamic Jurisdiction Fields -->
            <div id="jurisdictionFields" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6"></div>

            <!-- Profile Picture -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">Profile Picture</label>
                <input type="file" name="profile_picture" accept="image/*" 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>

            <!-- About -->
            <div class="mt-6">
                <label class="block text-sm text-zinc-400 mb-2">About Candidate</label>
                <textarea name="about" rows="5" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></textarea>
            </div>

            <div class="mt-10">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl font-semibold">
                    Save Candidate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
// ==================== STATIC DATA ====================

const countiesList = [
    {id: 1, name: "Mombasa"},
    {id: 2, name: "Kwale"},
    {id: 3, name: "Kilifi"},
    {id: 4, name: "Tana River"},
    {id: 5, name: "Lamu"},
    {id: 6, name: "Taita Taveta"},
    {id: 7, name: "Garissa"},
    {id: 8, name: "Wajir"},
    {id: 9, name: "Mandera"},
    {id: 10, name: "Marsabit"},
    {id: 11, name: "Isiolo"},
    {id: 12, name: "Meru"},
    {id: 13, name: "Tharaka Nithi"},
    {id: 14, name: "Embu"},
    {id: 15, name: "Kitui"},
    {id: 16, name: "Machakos"},
    {id: 17, name: "Makueni"},
    {id: 18, name: "Nyandarua"},
    {id: 19, name: "Nyeri"},
    {id: 20, name: "Kirinyaga"},
    {id: 21, name: "Murang'a"},
    {id: 22, name: "Kiambu"},
    {id: 23, name: "Turkana"},
    {id: 24, name: "West Pokot"},
    {id: 25, name: "Samburu"},
    {id: 26, name: "Trans Nzoia"},
    {id: 27, name: "Uasin Gishu"},
    {id: 28, name: "Elgeyo Marakwet"},
    {id: 29, name: "Nandi"},
    {id: 30, name: "Baringo"},
    {id: 31, name: "Laikipia"},
    {id: 32, name: "Nakuru"},
    {id: 33, name: "Narok"},
    {id: 34, name: "Kajiado"},
    {id: 35, name: "Kericho"},
    {id: 36, name: "Bomet"},
    {id: 37, name: "Kakamega"},
    {id: 38, name: "Vihiga"},
    {id: 39, name: "Bungoma"},
    {id: 40, name: "Busia"},
    {id: 41, name: "Siaya"},
    {id: 42, name: "Kisumu"},
    {id: 43, name: "Homa Bay"},
    {id: 44, name: "Migori"},
    {id: 45, name: "Kisii"},
    {id: 46, name: "Nyamira"},
    {id: 47, name: "Nairobi"}
];

const constituenciesList = [
    // Mombasa (County 1)
    {id: 1, name: "Changamwe", county_id: 1},
    {id: 2, name: "Jomvu", county_id: 1},
    {id: 3, name: "Kisauni", county_id: 1},
    {id: 4, name: "Nyali", county_id: 1},
    {id: 5, name: "Likoni", county_id: 1},
    {id: 6, name: "Mvita", county_id: 1},

    // Kwale (County 2)
    {id: 7, name: "Msambweni", county_id: 2},
    {id: 8, name: "Lunga Lunga", county_id: 2},
    {id: 9, name: "Matuga", county_id: 2},
    {id: 10, name: "Kinango", county_id: 2},

    // Kilifi (County 3)
    {id: 11, name: "Kilifi North", county_id: 3},
    {id: 12, name: "Kilifi South", county_id: 3},
    {id: 13, name: "Kaloleni", county_id: 3},
    {id: 14, name: "Rabai", county_id: 3},
    {id: 15, name: "Ganze", county_id: 3},
    {id: 16, name: "Malindi", county_id: 3},
    {id: 17, name: "Magarini", county_id: 3},

    // Tana River (County 4)
    {id: 18, name: "Garsen", county_id: 4},
    {id: 19, name: "Galole", county_id: 4},
    {id: 20, name: "Bura", county_id: 4},

    // Lamu (County 5)
    {id: 21, name: "Lamu East", county_id: 5},
    {id: 22, name: "Lamu West", county_id: 5},

    // Taita Taveta (County 6)
    {id: 23, name: "Taveta", county_id: 6},
    {id: 24, name: "Wundanyi", county_id: 6},
    {id: 25, name: "Mwatate", county_id: 6},
    {id: 26, name: "Voi", county_id: 6},

    // Garissa (County 7)
    {id: 27, name: "Garissa Township", county_id: 7},
    {id: 28, name: "Balambala", county_id: 7},
    {id: 29, name: "Lagdera", county_id: 7},
    {id: 30, name: "Dadaab", county_id: 7},
    {id: 31, name: "Fafi", county_id: 7},
    {id: 32, name: "Ijara", county_id: 7},

    // Wajir (County 8)
    {id: 33, name: "Wajir North", county_id: 8},
    {id: 34, name: "Wajir East", county_id: 8},
    {id: 35, name: "Tarbaj", county_id: 8},
    {id: 36, name: "Wajir West", county_id: 8},
    {id: 37, name: "Eldas", county_id: 8},
    {id: 38, name: "Wajir South", county_id: 8},

    // Mandera (County 9)
    {id: 39, name: "Mandera West", county_id: 9},
    {id: 40, name: "Banissa", county_id: 9},
    {id: 41, name: "Mandera North", county_id: 9},
    {id: 42, name: "Mandera South", county_id: 9},
    {id: 43, name: "Mandera East", county_id: 9},
    {id: 44, name: "Lafey", county_id: 9},

    // Marsabit (County 10)
    {id: 45, name: "Moyale", county_id: 10},
    {id: 46, name: "North Horr", county_id: 10},
    {id: 47, name: "Saku", county_id: 10},
    {id: 48, name: "Laisamis", county_id: 10},

    // Isiolo (County 11)
    {id: 49, name: "Isiolo North", county_id: 11},
    {id: 50, name: "Isiolo South", county_id: 11},

    // Meru (County 12)
    {id: 51, name: "Igembe South", county_id: 12},
    {id: 52, name: "Igembe Central", county_id: 12},
    {id: 53, name: "Igembe North", county_id: 12},
    {id: 54, name: "Tigania West", county_id: 12},
    {id: 55, name: "Tigania East", county_id: 12},
    {id: 56, name: "North Imenti", county_id: 12},
    {id: 57, name: "Buuri", county_id: 12},
    {id: 58, name: "Central Imenti", county_id: 12},
    {id: 59, name: "South Imenti", county_id: 12},

    // Tharaka Nithi (County 13)
    {id: 60, name: "Maara", county_id: 13},
    {id: 61, name: "Chuka/Igambang'ombe", county_id: 13},
    {id: 62, name: "Tharaka", county_id: 13},

    // Embu (County 14)
    {id: 63, name: "Manyatta", county_id: 14},
    {id: 64, name: "Runyenjes", county_id: 14},
    {id: 65, name: "Mbeere South", county_id: 14},
    {id: 66, name: "Mbeere North", county_id: 14},

    // Kitui (County 15)
    {id: 67, name: "Mwingi North", county_id: 15},
    {id: 68, name: "Mwingi West", county_id: 15},
    {id: 69, name: "Mwingi Central", county_id: 15},
    {id: 70, name: "Kitui West", county_id: 15},
    {id: 71, name: "Kitui Rural", county_id: 15},
    {id: 72, name: "Kitui Central", county_id: 15},
    {id: 73, name: "Kitui East", county_id: 15},
    {id: 74, name: "Kitui South", county_id: 15},

    // Machakos (County 16)
    {id: 75, name: "Masinga", county_id: 16},
    {id: 76, name: "Yatta", county_id: 16},
    {id: 77, name: "Kangundo", county_id: 16},
    {id: 78, name: "Matungulu", county_id: 16},
    {id: 79, name: "Kathiani", county_id: 16},
    {id: 80, name: "Mavoko", county_id: 16},
    {id: 81, name: "Machakos Town", county_id: 16},
    {id: 82, name: "Mwala", county_id: 16},

    // Makueni (County 17)
    {id: 83, name: "Mbooni", county_id: 17},
    {id: 84, name: "Kilome", county_id: 17},
    {id: 85, name: "Kaiti", county_id: 17},
    {id: 86, name: "Makueni", county_id: 17},
    {id: 87, name: "Kibwezi West", county_id: 17},
    {id: 88, name: "Kibwezi East", county_id: 17},

    // Nyandarua (County 18)
    {id: 89, name: "Kinangop", county_id: 18},
    {id: 90, name: "Kipipiri", county_id: 18},
    {id: 91, name: "Ol Kalou", county_id: 18},
    {id: 92, name: "Ol Jorok", county_id: 18},
    {id: 93, name: "Ndaragwa", county_id: 18},

    // Nyeri (County 19)
    {id: 94, name: "Tetu", county_id: 19},
    {id: 95, name: "Kieni", county_id: 19},
    {id: 96, name: "Mathira", county_id: 19},
    {id: 97, name: "Othaya", county_id: 19},
    {id: 98, name: "Mukurweini", county_id: 19},
    {id: 99, name: "Nyeri Town", county_id: 19},

    // Kirinyaga (County 20)
    {id: 100, name: "Mwea", county_id: 20},
    {id: 101, name: "Gichugu", county_id: 20},
    {id: 102, name: "Ndia", county_id: 20},
    {id: 103, name: "Kirinyaga Central", county_id: 20},

    // Murang'a (County 21)
    {id: 104, name: "Kangema", county_id: 21},
    {id: 105, name: "Mathioya", county_id: 21},
    {id: 106, name: "Kiharu", county_id: 21},
    {id: 107, name: "Kigumo", county_id: 21},
    {id: 108, name: "Maragwa", county_id: 21},
    {id: 109, name: "Kandara", county_id: 21},
    {id: 110, name: "Gatanga", county_id: 21},

    // Kiambu (County 22)
    {id: 111, name: "Gatundu South", county_id: 22},
    {id: 112, name: "Gatundu North", county_id: 22},
    {id: 113, name: "Juja", county_id: 22},
    {id: 114, name: "Thika Town", county_id: 22},
    {id: 115, name: "Ruiru", county_id: 22},
    {id: 116, name: "Githunguri", county_id: 22},
    {id: 117, name: "Kiambu", county_id: 22},
    {id: 118, name: "Kiambaa", county_id: 22},
    {id: 119, name: "Kabete", county_id: 22},
    {id: 120, name: "Kikuyu", county_id: 22},
    {id: 121, name: "Limuru", county_id: 22},
    {id: 122, name: "Lari", county_id: 22},

    // Turkana (County 23)
    {id: 123, name: "Turkana North", county_id: 23},
    {id: 124, name: "Turkana West", county_id: 23},
    {id: 125, name: "Turkana Central", county_id: 23},
    {id: 126, name: "Loima", county_id: 23},
    {id: 127, name: "Turkana South", county_id: 23},
    {id: 128, name: "Turkana East", county_id: 23},

    // West Pokot (County 24)
    {id: 129, name: "Kapenguria", county_id: 24},
    {id: 130, name: "Sigor", county_id: 24},
    {id: 131, name: "Kacheliba", county_id: 24},
    {id: 132, name: "Pokot South", county_id: 24},

    // Samburu (County 25)
    {id: 133, name: "Samburu West", county_id: 25},
    {id: 134, name: "Samburu North", county_id: 25},
    {id: 135, name: "Samburu East", county_id: 25},

    // Trans Nzoia (County 26)
    {id: 136, name: "Kwanza", county_id: 26},
    {id: 137, name: "Endebess", county_id: 26},
    {id: 138, name: "Saboti", county_id: 26},
    {id: 139, name: "Kiminini", county_id: 26},
    {id: 140, name: "Cherangany", county_id: 26},

    // Uasin Gishu (County 27)
    {id: 141, name: "Soy", county_id: 27},
    {id: 142, name: "Turbo", county_id: 27},
    {id: 143, name: "Moiben", county_id: 27},
    {id: 144, name: "Ainabkoi", county_id: 27},
    {id: 145, name: "Kapseret", county_id: 27},
    {id: 146, name: "Kesses", county_id: 27},

    // Elgeyo Marakwet (County 28)
    {id: 147, name: "Marakwet East", county_id: 28},
    {id: 148, name: "Marakwet West", county_id: 28},
    {id: 149, name: "Keiyo North", county_id: 28},
    {id: 150, name: "Keiyo South", county_id: 28},

    // Nandi (County 29)
    {id: 151, name: "Tinderet", county_id: 29},
    {id: 152, name: "Aldai", county_id: 29},
    {id: 153, name: "Nandi Hills", county_id: 29},
    {id: 154, name: "Chesumei", county_id: 29},
    {id: 155, name: "Emgwen", county_id: 29},
    {id: 156, name: "Mosop", county_id: 29},

    // Baringo (County 30)
    {id: 157, name: "Tiaty", county_id: 30},
    {id: 158, name: "Baringo North", county_id: 30},
    {id: 159, name: "Baringo Central", county_id: 30},
    {id: 160, name: "Baringo South", county_id: 30},
    {id: 161, name: "Mogotio", county_id: 30},
    {id: 162, name: "Eldama Ravine", county_id: 30},

    // Laikipia (County 31)
    {id: 163, name: "Laikipia West", county_id: 31},
    {id: 164, name: "Laikipia East", county_id: 31},
    {id: 165, name: "Laikipia North", county_id: 31},

    // Nakuru (County 32)
    {id: 166, name: "Molo", county_id: 32},
    {id: 167, name: "Njoro", county_id: 32},
    {id: 168, name: "Naivasha", county_id: 32},
    {id: 169, name: "Gilgil", county_id: 32},
    {id: 170, name: "Kuresoi South", county_id: 32},
    {id: 171, name: "Kuresoi North", county_id: 32},
    {id: 172, name: "Subukia", county_id: 32},
    {id: 173, name: "Rongai", county_id: 32},
    {id: 174, name: "Bahati", county_id: 32},
    {id: 175, name: "Nakuru Town West", county_id: 32},
    {id: 176, name: "Nakuru Town East", county_id: 32},

    // Narok (County 33)
    {id: 177, name: "Kilgoris", county_id: 33},
    {id: 178, name: "Emurua Dikirr", county_id: 33},
    {id: 179, name: "Narok North", county_id: 33},
    {id: 180, name: "Narok East", county_id: 33},
    {id: 181, name: "Narok South", county_id: 33},
    {id: 182, name: "Narok West", county_id: 33},

    // Kajiado (County 34)
    {id: 183, name: "Kajiado North", county_id: 34},
    {id: 184, name: "Kajiado Central", county_id: 34},
    {id: 185, name: "Kajiado East", county_id: 34},
    {id: 186, name: "Kajiado West", county_id: 34},
    {id: 187, name: "Kajiado South", county_id: 34},

    // Kericho (County 35)
    {id: 188, name: "Kipkelion East", county_id: 35},
    {id: 189, name: "Kipkelion West", county_id: 35},
    {id: 190, name: "Ainamoi", county_id: 35},
    {id: 191, name: "Bureti", county_id: 35},
    {id: 192, name: "Belgut", county_id: 35},
    {id: 193, name: "Sigowet–Soin", county_id: 35},

    // Bomet (County 36)
    {id: 194, name: "Sotik", county_id: 36},
    {id: 195, name: "Chepalungu", county_id: 36},
    {id: 196, name: "Bomet East", county_id: 36},
    {id: 197, name: "Bomet Central", county_id: 36},
    {id: 198, name: "Konoin", county_id: 36},

    // Kakamega (County 37)
    {id: 199, name: "Lugari", county_id: 37},
    {id: 200, name: "Likuyani", county_id: 37},
    {id: 201, name: "Malava", county_id: 37},
    {id: 202, name: "Lurambi", county_id: 37},
    {id: 203, name: "Navakholo", county_id: 37},
    {id: 204, name: "Mumias West", county_id: 37},
    {id: 205, name: "Mumias East", county_id: 37},
    {id: 206, name: "Matungu", county_id: 37},
    {id: 207, name: "Butere", county_id: 37},
    {id: 208, name: "Khwisero", county_id: 37},
    {id: 209, name: "Shinyalu", county_id: 37},
    {id: 210, name: "Ikolomani", county_id: 37},

    // Vihiga (County 38)
    {id: 211, name: "Vihiga", county_id: 38},
    {id: 212, name: "Sabatia", county_id: 38},
    {id: 213, name: "Hamisi", county_id: 38},
    {id: 214, name: "Luanda", county_id: 38},
    {id: 215, name: "Emuhaya", county_id: 38},

    // Bungoma (County 39)
    {id: 216, name: "Mount Elgon", county_id: 39},
    {id: 217, name: "Sirisia", county_id: 39},
    {id: 218, name: "Kabuchai", county_id: 39},
    {id: 219, name: "Bumula", county_id: 39},
    {id: 220, name: "Kanduyi", county_id: 39},
    {id: 221, name: "Webuye East", county_id: 39},
    {id: 222, name: "Webuye West", county_id: 39},
    {id: 223, name: "Kimilili", county_id: 39},
    {id: 224, name: "Tongaren", county_id: 39},

    // Busia (County 40)
    {id: 225, name: "Teso North", county_id: 40},
    {id: 226, name: "Teso South", county_id: 40},
    {id: 227, name: "Nambale", county_id: 40},
    {id: 228, name: "Matayos", county_id: 40},
    {id: 229, name: "Butula", county_id: 40},
    {id: 230, name: "Funyula", county_id: 40},
    {id: 231, name: "Budalangi", county_id: 40},

    // Siaya (County 41)
    {id: 232, name: "Ugenya", county_id: 41},
    {id: 233, name: "Ugunja", county_id: 41},
    {id: 234, name: "Alego Usonga", county_id: 41},
    {id: 235, name: "Gem", county_id: 41},
    {id: 236, name: "Bondo", county_id: 41},
    {id: 237, name: "Rarieda", county_id: 41},

    // Kisumu (County 42)
    {id: 238, name: "Kisumu East", county_id: 42},
    {id: 239, name: "Kisumu West", county_id: 42},
    {id: 240, name: "Kisumu Central", county_id: 42},
    {id: 241, name: "Seme", county_id: 42},
    {id: 242, name: "Nyando", county_id: 42},
    {id: 243, name: "Muhoroni", county_id: 42},
    {id: 244, name: "Nyakach", county_id: 42},

    // Homa Bay (County 43)
    {id: 245, name: "Kasipul", county_id: 43},
    {id: 246, name: "Kabondo Kasipul", county_id: 43},
    {id: 247, name: "Karachuonyo", county_id: 43},
    {id: 248, name: "Rangwe", county_id: 43},
    {id: 249, name: "Homa Bay Town", county_id: 43},
    {id: 250, name: "Ndhiwa", county_id: 43},
    {id: 251, name: "Suba North", county_id: 43},
    {id: 252, name: "Suba South", county_id: 43},

    // Migori (County 44)
    {id: 253, name: "Rongo", county_id: 44},
    {id: 254, name: "Awendo", county_id: 44},
    {id: 255, name: "Suna East", county_id: 44},
    {id: 256, name: "Suna West", county_id: 44},
    {id: 257, name: "Uriri", county_id: 44},
    {id: 258, name: "Nyatike", county_id: 44},
    {id: 259, name: "Kuria West", county_id: 44},
    {id: 260, name: "Kuria East", county_id: 44},

    // Kisii (County 45)
    {id: 261, name: "Bonchari", county_id: 45},
    {id: 262, name: "South Mugirango", county_id: 45},
    {id: 263, name: "Bomachoge Borabu", county_id: 45},
    {id: 264, name: "Bobasi", county_id: 45},
    {id: 265, name: "Bomachoge Chache", county_id: 45},
    {id: 266, name: "Nyaribari Masaba", county_id: 45},
    {id: 267, name: "Nyaribari Chache", county_id: 45},
    {id: 268, name: "Kitutu Chache North", county_id: 45},
    {id: 269, name: "Kitutu Chache South", county_id: 45},

    // Nyamira (County 46)
    {id: 270, name: "Kitutu Masaba", county_id: 46},
    {id: 271, name: "West Mugirango", county_id: 46},
    {id: 272, name: "North Mugirango", county_id: 46},
    {id: 273, name: "Borabu", county_id: 46},

    // Nairobi (County 47)
    {id: 274, name: "Westlands", county_id: 47},
    {id: 275, name: "Dagoretti North", county_id: 47},
    {id: 276, name: "Dagoretti South", county_id: 47},
    {id: 277, name: "Lang'ata", county_id: 47},
    {id: 278, name: "Kibra", county_id: 47},
    {id: 279, name: "Roysambu", county_id: 47},
    {id: 280, name: "Kasarani", county_id: 47},
    {id: 281, name: "Ruaraka", county_id: 47},
    {id: 282, name: "Embakasi South", county_id: 47},
    {id: 283, name: "Embakasi North", county_id: 47},
    {id: 284, name: "Embakasi Central", county_id: 47},
    {id: 285, name: "Embakasi East", county_id: 47},
    {id: 286, name: "Embakasi West", county_id: 47},
    {id: 287, name: "Makadara", county_id: 47},
    {id: 288, name: "Kamukunji", county_id: 47},
    {id: 289, name: "Starehe", county_id: 47},
    {id: 290, name: "Mathare", county_id: 47}
];

const wardsList = [
    // === MOMBASA (County 1) ===
    {id: 1, name: "Port Reitz", constituency_id: 1},
    {id: 2, name: "Kipevu", constituency_id: 1},
    {id: 3, name: "Airport", constituency_id: 1},
    {id: 4, name: "Changamwe", constituency_id: 1},
    {id: 5, name: "Chaani", constituency_id: 1},
    {id: 6, name: "Jomvu Kuu", constituency_id: 2},
    {id: 7, name: "Miritini", constituency_id: 2},
    {id: 8, name: "Mikindani", constituency_id: 2},
    {id: 9, name: "Mjambere", constituency_id: 3},
    {id: 10, name: "Junda", constituency_id: 3},
    {id: 11, name: "Bamburi", constituency_id: 3},
    {id: 12, name: "Mwakirunge", constituency_id: 3},
    {id: 13, name: "Mtopanga", constituency_id: 3},
    {id: 14, name: "Magogoni", constituency_id: 3},
    {id: 15, name: "Shanzu", constituency_id: 3},
    {id: 16, name: "Frere Town", constituency_id: 4},
    {id: 17, name: "Ziwa La Ng'ombe", constituency_id: 4},
    {id: 18, name: "Mkomani", constituency_id: 4},
    {id: 19, name: "Kongowea", constituency_id: 4},
    {id: 20, name: "Kadzandani", constituency_id: 4},
    {id: 21, name: "Mtongwe", constituency_id: 5},
    {id: 22, name: "Shika Adabu", constituency_id: 5},
    {id: 23, name: "Bofu", constituency_id: 5},
    {id: 24, name: "Likoni", constituency_id: 5},
    {id: 25, name: "Timbwani", constituency_id: 5},
    {id: 26, name: "Mji Wa Kale/Makadara", constituency_id: 6},
    {id: 27, name: "Tudor", constituency_id: 6},
    {id: 28, name: "Tononoka", constituency_id: 6},
    {id: 29, name: "Shimanzi/Ganjoni", constituency_id: 6},
    {id: 30, name: "Majengo", constituency_id: 6},

    // === KWALE (County 2) ===
    {id: 31, name: "Gombato Bongwe", constituency_id: 7},
    {id: 32, name: "Ukunda", constituency_id: 7},
    {id: 33, name: "Kinondo", constituency_id: 7},
    {id: 34, name: "Ramisi", constituency_id: 7},
    {id: 35, name: "Pongwe Kikoneni", constituency_id: 8},
    {id: 36, name: "Dzombo", constituency_id: 8},
    {id: 37, name: "Mwereni", constituency_id: 8},
    {id: 38, name: "Vanga", constituency_id: 8},
    {id: 39, name: "Tsimba Golini", constituency_id: 9},
    {id: 40, name: "Waa", constituency_id: 9},
    {id: 41, name: "Tiwi", constituency_id: 9},
    {id: 42, name: "Kubo South", constituency_id: 9},
    {id: 43, name: "Mkongoani", constituency_id: 9},
    {id: 44, name: "Ndavaya", constituency_id: 10},
    {id: 45, name: "Puma", constituency_id: 10},
    {id: 46, name: "Kinango", constituency_id: 10},
    {id: 47, name: "Mackinnon Road", constituency_id: 10},
    {id: 48, name: "Chengoni/Samburu", constituency_id: 10},
    {id: 49, name: "Mwavumbo", constituency_id: 10},
    {id: 50, name: "Kasemeni", constituency_id: 10},

    // === KILIFI (County 3) ===
    {id: 51, name: "Tezo", constituency_id: 11},
    {id: 52, name: "Sokoni", constituency_id: 11},
    {id: 53, name: "Kibarani", constituency_id: 11},
    {id: 54, name: "Dabaso", constituency_id: 11},
    {id: 55, name: "Matsangoni", constituency_id: 11},
    {id: 56, name: "Watamu", constituency_id: 11},
    {id: 57, name: "Mnarani", constituency_id: 11},
    {id: 58, name: "Junju", constituency_id: 12},
    {id: 59, name: "Mwarakaya", constituency_id: 12},
    {id: 60, name: "Shimo La Tewa", constituency_id: 12},
    {id: 61, name: "Chasimba", constituency_id: 12},
    {id: 62, name: "Mtepeni", constituency_id: 12},
    {id: 63, name: "Mariakani", constituency_id: 13},
    {id: 64, name: "Kayafungo", constituency_id: 13},
    {id: 65, name: "Kaloleni", constituency_id: 13},
    {id: 66, name: "Mwanamwinga", constituency_id: 13},
    {id: 67, name: "Mwawesa", constituency_id: 14},
    {id: 68, name: "Ruruma", constituency_id: 14},
    {id: 69, name: "Kambe/Ribe", constituency_id: 14},
    {id: 70, name: "Rabai/Kisurutini", constituency_id: 14},
    {id: 71, name: "Ganze", constituency_id: 15},
    {id: 72, name: "Bamba", constituency_id: 15},
    {id: 73, name: "Jaribuni", constituency_id: 15},
    {id: 74, name: "Sokoke", constituency_id: 15},
    {id: 75, name: "Jilore", constituency_id: 16},
    {id: 76, name: "Kakuyuni", constituency_id: 16},
    {id: 77, name: "Ganda", constituency_id: 16},
    {id: 78, name: "Malindi Town", constituency_id: 16},
    {id: 79, name: "Shella", constituency_id: 16},
    {id: 80, name: "Marafa", constituency_id: 17},
    {id: 81, name: "Magarini", constituency_id: 17},
    {id: 82, name: "Gongoni", constituency_id: 17},
    {id: 83, name: "Adu", constituency_id: 17},
    {id: 84, name: "Garashi", constituency_id: 17},
    {id: 85, name: "Sabaki", constituency_id: 17},

    // === TANA RIVER (County 4) ===
    {id: 86, name: "Kipini East", constituency_id: 18},
    {id: 87, name: "Garsen South", constituency_id: 18},
    {id: 88, name: "Kipini West", constituency_id: 18},
    {id: 89, name: "Garsen Central", constituency_id: 18},
    {id: 90, name: "Garsen West", constituency_id: 18},
    {id: 91, name: "Garsen North", constituency_id: 18},
    {id: 92, name: "Kinakomba", constituency_id: 19},
    {id: 93, name: "Mikinduni", constituency_id: 19},
    {id: 94, name: "Chewani", constituency_id: 19},
    {id: 95, name: "Wayu", constituency_id: 19},
    {id: 96, name: "Chewele", constituency_id: 20},
    {id: 97, name: "Bura", constituency_id: 20},
    {id: 98, name: "Bangale", constituency_id: 20},
    {id: 99, name: "Sala", constituency_id: 20},
    {id: 100, name: "Madogo", constituency_id: 20},

    // === LAMU (County 5) ===
    {id: 101, name: "Faza", constituency_id: 21},
    {id: 102, name: "Kiunga", constituency_id: 21},
    {id: 103, name: "Basuba", constituency_id: 21},
    {id: 104, name: "Shella", constituency_id: 22},
    {id: 105, name: "Mkomani", constituency_id: 22},
    {id: 106, name: "Hindi", constituency_id: 22},
    {id: 107, name: "Mkunumbi", constituency_id: 22},
    {id: 108, name: "Hongwe", constituency_id: 22},
    {id: 109, name: "Witu", constituency_id: 22},
    {id: 110, name: "Bahari", constituency_id: 22},

    // === TAITA TAVETA (County 6) ===
    {id: 111, name: "Chala", constituency_id: 23},
    {id: 112, name: "Mahoo", constituency_id: 23},
    {id: 113, name: "Bomeni", constituency_id: 23},
    {id: 114, name: "Mboghoni", constituency_id: 23},
    {id: 115, name: "Mata", constituency_id: 23},
    {id: 116, name: "Wundanyi/Mbale", constituency_id: 24},
    {id: 117, name: "Werugha", constituency_id: 24},
    {id: 118, name: "Wumingu/Kishushe", constituency_id: 24},
    {id: 119, name: "Mwanda/Mgange", constituency_id: 24},
    {id: 120, name: "Rong'e", constituency_id: 25},
    {id: 121, name: "Mwatate", constituency_id: 25},
    {id: 122, name: "Bura", constituency_id: 25},
    {id: 123, name: "Chawia", constituency_id: 25},
    {id: 124, name: "Wusi/Kishamba", constituency_id: 25},
    {id: 125, name: "Mbololo", constituency_id: 26},
    {id: 126, name: "Sagalla", constituency_id: 26},
    {id: 127, name: "Kaloleni", constituency_id: 26},
    {id: 128, name: "Marungu", constituency_id: 26},
    {id: 129, name: "Kasigau", constituency_id: 26},
    {id: 130, name: "Ngolia", constituency_id: 26}
];

// =====================================================

const positionSelect = document.getElementById('positionSelect');
const jurisdictionFields = document.getElementById('jurisdictionFields');

function populateSelect(selectElement, options, defaultText = "Select...") {
    selectElement.innerHTML = `<option value="">${defaultText}</option>`;
    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option.id;
        opt.textContent = option.name;
        selectElement.appendChild(opt);
    });
}

function renderJurisdictionFields(positionName) {
    let html = '';

    const isPresident = positionName.includes('president');
    const isGovernor = positionName.includes('governor') || positionName.includes('senator') || positionName.includes('women representative');
    const isMP = positionName.includes('mp') || positionName.includes('member of parliament');
    const isMCA = positionName.includes('mca') || positionName.includes('county assembly');

    if (isPresident) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">Country</label>
                <input type="text" name="country" value="Kenya" readonly 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>`;
    } 
    else if (isGovernor) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">County <span class="text-red-500">*</span></label>
                <select name="county_id" id="countySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>`;
    } 
    else if (isMP) {
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2">Constituency <span class="text-red-500">*</span></label>
                <select name="constituency_id" id="constituencySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>`;
    } 
    else if (isMCA) {
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency_id" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward <span class="text-red-500">*</span></label>
                <select name="ward_id" id="wardSelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Ward</option>
                </select>
            </div>`;
    } 
    else {
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency_id" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward</label>
                <select name="ward_id" id="wardSelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>`;
    }

    jurisdictionFields.innerHTML = html;
    attachDynamicListeners();
}

function attachDynamicListeners() {
    const countySelect = document.getElementById('countySelect');
    const constituencySelect = document.getElementById('constituencySelect');
    const wardSelect = document.getElementById('wardSelect');

    if (countySelect) {
        populateSelect(countySelect, countiesList, "Select County");

        countySelect.addEventListener('change', function() {
            const countyId = parseInt(this.value);
            
            if (constituencySelect) {
                const filtered = constituenciesList.filter(c => c.county_id === countyId);
                populateSelect(constituencySelect, filtered, "Select Constituency");
            }
            
            if (wardSelect) {
                wardSelect.innerHTML = '<option value="">Select Ward</option>';
            }
        });
    }

    if (constituencySelect) {
        constituencySelect.addEventListener('change', function() {
            const consId = parseInt(this.value);
            if (wardSelect) {
                const filtered = wardsList.filter(w => w.constituency_id === consId);
                populateSelect(wardSelect, filtered, "Select Ward");
            }
        });
    }
}

// Main Event Listener
positionSelect.addEventListener('change', function() {
    const positionName = this.options[this.selectedIndex].text.toLowerCase().trim();
    renderJurisdictionFields(positionName);
});

// Optional: Trigger default on page load if needed
</script>
@endpush

<!-- @push('scripts')
<script>
const positionSelect = document.getElementById('positionSelect');
const jurisdictionFields = document.getElementById('jurisdictionFields');

let allCounties = [];

// Fetch all counties once
async function fetchCounties() {
    try {
        const res = await fetch('/api/counties');
        allCounties = await res.json();
    } catch (e) {
        console.error('Failed to load counties', e);
    }
}

// Fetch constituencies by county
async function fetchConstituencies(countyId) {
    if (!countyId) return [];
    const res = await fetch(`/api/constituencies?county_id=${countyId}`);
    return await res.json();
}

// Fetch wards by constituency
async function fetchWards(constituencyId) {
    if (!constituencyId) return [];
    const res = await fetch(`/api/wards?constituency_id=${constituencyId}`);
    return await res.json();
}

function renderJurisdictionFields(positionName) {
    let html = '';

    const isPresident = positionName.includes('president');
    const isGovernor = positionName.includes('governor') || positionName.includes('senator') || positionName.includes('women representative');
    const isMP = positionName.includes('mp') || positionName.includes('member of parliament');
    const isMCA = positionName.includes('mca') || positionName.includes('county assembly');

    if (isPresident) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">Country</label>
                <input type="text" name="country" value="Kenya" readonly 
                       class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
            </div>`;
    } 
    else if (isGovernor) {
        html = `
            <div class="md:col-span-3">
                <label class="block text-sm text-zinc-400 mb-2">County <span class="text-red-500">*</span></label>
                <select name="county_id" id="countySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>`;
    } 
    else if (isMP) {
        html = `
        
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-zinc-400 mb-2">Constituency <span class="text-red-500">*</span></label>
                <select name="constituency_id" id="constituencySelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>`;
    } 
    else if (isMCA) {
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select County</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency_id" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Constituency</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward <span class="text-red-500">*</span></label>
                <select name="ward_id" id="wardSelect" required 
                        class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white">
                    <option value="">Select Ward</option>
                </select>
            </div>`;
    } 
    else {
        // Default fallback
        html = `
            <div>
                <label class="block text-sm text-zinc-400 mb-2">County</label>
                <select name="county_id" id="countySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Constituency</label>
                <select name="constituency_id" id="constituencySelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>
            <div>
                <label class="block text-sm text-zinc-400 mb-2">Ward</label>
                <select name="ward_id" id="wardSelect" class="w-full bg-zinc-800 border border-zinc-700 rounded-2xl px-4 py-3 text-white"></select>
            </div>`;
    }

    jurisdictionFields.innerHTML = html;
    attachEventListeners();
}

function attachEventListeners() {
    const countySelect = document.getElementById('countySelect');
    const constituencySelect = document.getElementById('constituencySelect');
    const wardSelect = document.getElementById('wardSelect');

    if (countySelect) {
        // Populate counties
        allCounties.forEach(county => {
            const opt = document.createElement('option');
            opt.value = county.id;
            opt.textContent = county.name;
            countySelect.appendChild(opt);
        });

        countySelect.addEventListener('change', async function() {
            const countyId = this.value;
            if (!constituencySelect) return;

            constituencySelect.innerHTML = '<option value="">Loading...</option>';
            const data = await fetchConstituencies(countyId);

            constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
            data.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                constituencySelect.appendChild(opt);
            });

            // Reset ward if exists
            if (wardSelect) wardSelect.innerHTML = '<option value="">Select Ward</option>';
        });
    }

    if (constituencySelect) {
        constituencySelect.addEventListener('change', async function() {
            const consId = this.value;
            if (!wardSelect) return;

            wardSelect.innerHTML = '<option value="">Loading...</option>';
            const data = await fetchWards(consId);

            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            data.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.textContent = w.name;
                wardSelect.appendChild(opt);
            });
        });
    }
}

// Main listener
positionSelect.addEventListener('change', function() {
    const positionName = this.options[this.selectedIndex].text.toLowerCase().trim();
    renderJurisdictionFields(positionName);
});

// Initialize
fetchCounties();
</script>
@endpush -->