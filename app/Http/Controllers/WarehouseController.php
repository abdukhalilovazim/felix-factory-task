<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseResource;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'remainder' => 'required|numeric|gt:0',
            'price' => 'required|numeric|gt:0',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $model = Warehouse::create(
            [
                'material_id' => $request->material_id,
                'remainder' => $request->remainder,
                'price' => $request->price,
            ]
        );
        return new WarehouseResource($model);
    }

    public function getProductMaterials(Request $request): \Illuminate\Http\JsonResponse
    {
        // JSON so'rovini olish
        $requestData = $request->json()->all();

        // JSON so'rovidan mahsulot miqdorini olish
        $productQuantities = $this->getProductQuantities($requestData['data']);

        // Har bir mahsulot uchun materiallarni olish
        $results = $this->calculateRequiredMaterials($productQuantities);

        return response()->json(['result' => $results]);
    }

    // Mahsulot miqdorini olish uchun
    private function getProductQuantities($data): array
    {
        $productQuantities = [];

        foreach ($data as $item) {
            $productQuantities[$item['product_id']] = $item['qty'];
        }
        return $productQuantities;
    }

    private function calculateRequiredMaterials($productQuantities): array
    {
        // Natijani saqlash uchun
        $results = [];

        // Ishlatilgan materialni aniqlash uchun
        $usedWarehouses = [];

        // So'rovda nechta mahsulot yuborilgan bo'lsa shuncha marta ishlaydi
        foreach ($productQuantities as $productId => $quantity) {

            // Mahsulotni topish
            $product = Product::find($productId);
            if ($product) {

                // Mahsulot uchun kerakli materiallarni olish
                $requiredMaterials = $this->getProductMaterialsForProduct($product, $quantity, $usedWarehouses);

                // Materiallarni mahsulotga qo'shib yuborish
                $results[] = [
                    'product_name' => $product->name,
                    'product_qty' => $quantity,
                    'product_materials' => $requiredMaterials,
                ];
            }
        }

        return $results;
    }

    // Mahsulot uchun qaysi materiallar kerakligini aniqlaydigan function
    private function getProductMaterialsForProduct($product, $quantity, &$usedWarehouses): array
    {
        // Joriy mahsulot uchun kerakli materiallarni saqlash uchun arrayni ishga tushirish
        $requiredMaterials = [];

        // Mahsulotning har bir materialini takrorlash
        foreach ($product->materials as $material) {

            // Mahsulot va material asosida qolgan miqdorni hisoblang
            $remainingQuantity = $this->getRemainingQuantity($product, $material, $quantity);

            // Joriy material uchun omborlarni oling
            $warehouses = Warehouse::where('material_id', $material->id)->orderBy('id')->get();

            // Qolgan miqdorni tekshirish uchun omborlarni qayta ishlash
            $this->processWarehouses($warehouses, $material, $remainingQuantity, $usedWarehouses, $requiredMaterials);
        }

        // Joriy mahsulot uchun kerakli materiallar qatorini qaytarish
        return $requiredMaterials;
    }

    // Mahsulot uchun qancha material kerakligini hisoblash
    private function getRemainingQuantity($product, $material, $quantity): float|int
    {
        return $product->productMaterials->where('material_id', $material->id)->first()->quantity * $quantity;
    }

    private function processWarehouses($warehouses, $material, &$remainingQuantity, &$usedWarehouses, &$requiredMaterials)
    {
        // Joriy material uchun har bir omborni takrorlash
        foreach ($warehouses as $warehouse) {

            // Joriy ombordan olinadigan miqdorni hisoblash
            $takeQuantity = $this->calculateTakeQuantity($warehouse, $remainingQuantity, $usedWarehouses);

            // Qabul qilinadigan miqdor bo'lsa, ishlatilgan omborlarni yangilang va kerakli materiallarga qo'shish
            if ($takeQuantity > 0) {
                $this->updateUsedWarehouses($warehouse, $takeQuantity, $usedWarehouses);
                $requiredMaterials[] = $this->createMaterialData($warehouse, $material, $takeQuantity);
                $remainingQuantity -= $takeQuantity;

                // Agar qolgan miqdor bajarilsa, tsiklni to'xtatish
                if ($remainingQuantity <= 0) {
                    break;
                }
            }
        }

        // Agar material soni yetmasa, uni kerakli materiallarga alohida yozuv sifatida qo'shish
        if ($remainingQuantity > 0) {
            $requiredMaterials[] = [
                'warehouse_id' => null,
                'material_name' => $material->name,
                'qty' => $remainingQuantity,
                'price' => null,
            ];
        }
    }

    private function calculateTakeQuantity($warehouse, $remainingQuantity, $usedWarehouses)
    {
        // Joriy ombordan foydalanilgan miqdor haqida ma'lumot olish
        $usedWarehouse = $usedWarehouses[$warehouse->id] ?? ['qty' => 0];

        // Ombordagi mavjud miqdorni hisoblash
        $availableQuantity = $warehouse->remainder - $usedWarehouse['qty'];

        // Qolgan miqdor va mavjud miqdor o'rtasidagi minimal qiymatni qaytarish
        return min($remainingQuantity, $availableQuantity);
    }

    private function updateUsedWarehouses($warehouse, $takeQuantity, &$usedWarehouses)
    {
        // Joriy ombordan foydalanilgan miqdor haqida ma'lumot olish
        $usedWarehouse = $usedWarehouses[$warehouse->id] ?? ['qty' => 0];

        // Ishlatilgan omborlar qatoridagi ishlatilgan miqdorni yangilash
        $usedWarehouses[$warehouse->id]['qty'] = $usedWarehouse['qty'] + $takeQuantity;
    }

    private function createMaterialData($warehouse, $material, $takeQuantity): array
    {
        // Joriy ombordan kerakli material haqidagi ma'lumotlar bilan massiv yaratish
        return [
            'warehouse_id' => $warehouse->id,
            'material_name' => $material->name,
            'qty' => $takeQuantity,
            'price' => $warehouse->price,
        ];
    }

}
