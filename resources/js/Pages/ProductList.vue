<template>
    <Head title="Product List" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Product List</h2>
        </template>

        <div class="p-6 max-w-4xl mx-auto">
         <!-- Tombol Tambah Produk -->
            <div v-if="products.length < 5" class="mb-4">
                <button @click="showForm = true" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    ➕ Tambah Produk
                </button>
            </div>
            <div v-else class="text-red-600 font-medium mb-4">
                Anda Sudah Menerima Input Maksimum
            </div>

            <!-- Form Tambah Produk -->
            <div v-if="showForm" class="border p-4 rounded shadow mb-6">
                <h3 class="font-semibold text-lg mb-4">Form Tambah Produk</h3>
                <input v-model="form.name" placeholder="Nama Produk" class="w-full border px-3 py-2 rounded mb-2" type="text" />
                <textarea v-model="form.description" placeholder="Deskripsi Produk" class="w-full border px-3 py-2 rounded mb-2"></textarea>
                <input type="file" @change="handleFileUpload" class="w-full mb-2" />

                <div class="flex justify-end gap-2">
                    <button @click="cancelForm" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
                    <button @click="addProduct" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                </div>
            </div>

            <!-- Tombol Submit Keseluruhan -->
            <div class="mt-6">
                <button @click="submit" class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded">
                    Submit Semua Produk
                </button>
            </div>
            <h2 class="text-xl font-bold my-4">Daftar Produk</h2>
            <table class="w-full border-collapse border mb-6">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-4 py-2">No</th>
                        <th class="border px-4 py-2">Nama Produk</th>
                        <th class="border px-4 py-2">Deskripsi</th>
                        <th class="border px-4 py-2">Gambar</th>
                        <th class="border px-4 py-2">Aksi</th>
                    </tr>
                </thead>
               <tbody>
                <tr v-for="(product, index) in products" :key="index">
                    <td class="border px-4 py-2 text-center">{{ index + 1 }}</td>
                    <td class="border px-4 py-2 text-center">{{ product.name || '-' }}</td>
                    <td class="border px-4 py-2 text-center">{{ product.description || '-' }}</td>
                    <td class="border px-4 py-2 text-center">
                    <!-- Jika ada gambar -->
                    <div v-if="product.image" class="flex flex-col items-center gap-1">
                       <img v-if="product.image" 
                            :src="typeof product.image === 'string' 
                                    ? `/storage/images/${product.image}` 
                                    : URL.createObjectURL(product.image)" 
                            alt="Gambar Produk" 
                            class="h-16 mx-auto" />
                        <button @click="removeImage(index)" class="text-xl">❌</button>
                    </div>
                    <!-- Jika belum ada gambar -->
                    <div v-else class="flex flex-col items-center gap-1">
                        <label class="cursor-pointer text-xl">
                        📎
                        <input type="file" class="hidden" @change="handleFileChange($event, index)" />
                        </label>
                    </div>
                    </td>
                    <td class="border px-4 py-2 text-center">
                    <div class="flex items-center justify-center gap-2">
                       <button @click="deleteProduct(product.id)" class="text-red-600 hover:underline">×</button>
                        <button @click="addProductRow" class="text-xl">＋</button>
                    </div>
                    </td>
                </tr>
                </tbody>
                <div v-if="products.length === 0" class="flex justify-center py-4">
                 Tidak ada produk
                </div>
            </table>     
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'

const page = usePage()
const initialProducts = page.props.products ?? []
const products = ref([...initialProducts])


// const products = ref([])

const form = ref({
    name: '',
    description: '',
    image: null
})

const showForm = ref(false)

const handleFileChange = (event, index) => {
  const file = event.target.files[0]
  if (file) {
    products.value[index].image = file
  }
}

const removeImage = (index) => {
  const image = products.value[index].image
  if (image && typeof image !== 'string') {
    URL.revokeObjectURL(image)
  }
  products.value[index].image = null
}


const addProductRow = () => {
  if (products.value.length < 5) {
    products.value.push({ name: '', description: '', image: null })
  }
}

const addProduct = () => {
    if (products.value.length >= 5) return;

    if (!form.value.name || !form.value.description) {
        alert("Nama dan deskripsi produk wajib diisi.");
        return;
    }

    products.value.push({
        name: form.value.name,
        description: form.value.description,
        image: form.value.image,
    });

    resetForm();
    showForm.value = false;
};



const deleteProduct = (id) => {
    if (!confirm('Yakin ingin menghapus produk ini?')) return;

    router.delete(`/products/${id}`, {
        onSuccess: () => {
            products.value = products.value.filter(p => p.id !== id)
        }
    })
}



const cancelForm = () => {
    resetForm()
    showForm.value = false
}

const resetForm = () => {
    form.value = { name: '', description: '', image: null }
}

watch(() => page.props.products, (newProducts) => {
    products.value = [...newProducts]
})


const submit = () => {
    const formData = new FormData()

    products.value.forEach((product, i) => {
        formData.append(`products[${i}][name]`, product.name)
        formData.append(`products[${i}][description]`, product.description)
        if (product.image) {
            formData.append(`products[${i}][image]`, product.image)
        }
    })
    router.post('/products/store', formData, {
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/products/create', {
                preserveScroll: true,
                onSuccess: (page) => {
                    products.value = page.props.products
                }
            })
        }
    })
}

</script>
