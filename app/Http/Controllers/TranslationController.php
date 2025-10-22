<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Responses\ApiResponse;

use App\Models\TranslationGroup;
use App\Models\TranslationKey;

class TranslationController extends BaseController
{

    /**
     * @OA\Get(
     *      path="/api/translations/{language}",
     *      operationId="getTranslations",
     *      tags={"Переводы"},
     *      summary="Получение списка переводов по коду языка",
     *      description="Returns translations data for a given language",
     *      @OA\Parameter(
     *          name="language",
     *          description="Код языка, например: en, ru, ky",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Translation")
     *       ),
     *     @OA\Response(
     *         response="400",
     *         description="Неверный запрос",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     ),
     * ),
     * @OA\Schema(
     *   schema="Translation",
     *   type="object",
     *   @OA\Property(
     *      property="translationKey",
     *      type="string",
     *      description="Ключ перевода",
     *      example="hello_world"
     *   ),
     *   @OA\Property(
     *      property="translationValue",
     *      type="string",
     *      description="Текст перевода",
     *      example="Привет, Мир"
     *   )
     * )
     */
    public function getTranslations(Request $request, $language = null)
    {
        $language = strtolower(trim($language));

        if (empty($language) || !in_array($language, config('app.locales'))) {
            return ApiResponse::error('Переводы не найдены', 404);
        }

        $translations = [];

        foreach (TranslationGroup::orderBy('key_name', 'ASC')->get() as $res) {

            $base_key = trim($res->key_name);

            foreach ($res->keys as $key) {
                $full_key = strtolower($base_key.'_'.trim($key->key_name));

                $translations[$full_key] = $full_key;

                foreach ($key->translations as $translation) {
                    if ($translation->locale === $language) {
                        $translations[$full_key] = $translation->key_value;
                    }
                }

            }

        }

        return ApiResponse::success($translations, $language);
    }

    public function index(Request $request)
    {
        $q = $request->input('q', '');
        $options = $request->input('options', []);
        
        $sortBy = isset($options['sortBy'][0]['key']) ? $options['sortBy'][0]['key'] : 'name';
        $sortOrder = isset($options['sortBy'][0]['order']) ? $options['sortBy'][0]['order'] : 'asc';
        
        $page = isset($options['page']) ? intval($options['page']) : 1;
        $limit = isset($options['itemsPerPage']) ? intval($options['itemsPerPage']) : 50;
        $skip = ($page > 1) ? $page * $limit : 0;

        $result = TranslationGroup::withCount(['keys'])->where(function($query) use($q) {
            if(!empty($q)) {
                $query->where('name', 'like', '%' . $q . '%');
                $query->orWhere('key_name', 'like', '%' . $q . '%');
            }
        })->offset($skip)->limit($limit)->orderBy($sortBy, $sortOrder)->get();

        $total = $result->count();
        $data = $result->toArray();

        return ApiResponse::success([
            'total' => $total, 'page' => $page, 'items' => $result
        ]);
    }

    public function show($id, Request $request)
    {
        $res = TranslationGroup::findOrFail($id);
        return ApiResponse::success($res->toArray());
    }

    public function create(Request $request)
    {
        $rules = array(
            'name' => 'required|string',
            'key_name' => 'required|string|unique:translation_groups,key_name',
        );

        $data = $request->all();

        $validator = \Validator::make($data, $rules);

        if ($validator->fails()) {
            return ApiResponse::error('Проверьте данные');
        }

        $translationGroup = new TranslationGroup;
        $translationGroup->name = $data['name'];
        $translationGroup->key_name = $data['key_name'];
        $translationGroup->save();

        if (!empty($data['keys'])) {
            foreach($data['keys'] as $key_data) {

                $key_name = trim($key_data['key_name']);
                if (empty($key_name)) continue;

                $translationKey = new TranslationKey;
                $translationKey->group_id = $translationGroup->id;
                $translationKey->group_key_name = $translationGroup->key_name;
                $translationKey->key_name = $key_data['key_name'];
                $translationKey->name = $key_data['name'];
                $translationKey->save();

                if (!empty($key_data['values'])) {
                    foreach($key_data['values'] as $locale => $translate) {

                        $translationValue = $translationKey->translateOrNew($locale);
                        $translationValue->key_value = $translate;
                        $translationValue->translation_key_id = $translationKey->id;
                        $translationValue->save();
                        
                    }
                }

                $translationKey->save();

            }
        }

        return ApiResponse::success($translationGroup->toArray(), 'Группа переводов создана', 201);
    }

    public function edit($id)
    {
        $res = TranslationGroup::findOrFail($id);
        $translationGroup = $res->toArray();
        foreach ($translationGroup['keys'] as &$key) {
            $key['values'] = [];

            foreach ($key['translations'] as $translation) {
                $key['values'][$translation['locale']] = $translation['key_value'];
            }

        }

        return ApiResponse::success($translationGroup);
    }

    public function update($id, Request $request)
    {
        $rules = array(
            'id' => 'required|integer',
            'name' => 'required|string',
            'key_name' => ['required', 'string', Rule::unique('translation_groups', 'key_name')->ignore($request->id)],
        );

        $data = $request->all();

        $validator = \Validator::make($data, $rules);

        if ($validator->fails()) {
            return ApiResponse::error('Проверьте данные');
        }

        $translationGroup = TranslationGroup::find($data['id']);
        if (!$translationGroup) {
            return ApiResponse::error('Элемент не найден', 404);
        }
    
        $translationGroup->name = $data['name'];
        $translationGroup->key_name = $data['key_name'];
        $translationGroup->save();
        
        $translationGroup->keys()->delete();

        if (!empty($data['keys'])) {
            foreach($data['keys'] as $key_data) {

                $key_name = trim($key_data['key_name']);
                if (empty($key_name)) continue;

                $translationKey = new TranslationKey;
                $translationKey->group_id = $translationGroup->id;
                $translationKey->group_key_name = $translationGroup->key_name;
                $translationKey->key_name = $key_data['key_name'];
                $translationKey->name = $key_data['name'];
                $translationKey->save();

                if (!empty($key_data['values'])) {
                    foreach($key_data['values'] as $locale => $translate) {
                        $translationValue = $translationKey->translateOrNew($locale);
                        $translationValue->key_value = $translate;
                        $translationValue->translation_key_id = $translationKey->id;
                        $translationValue->save();
                    }
                }

                $translationKey->save();

            }
        }

        return ApiResponse::success($translationGroup->toArray(), 'Группа переводов обновлена');
    }

    public function destroy($id)
    {
        $res = TranslationGroup::findOrFail($id);
        $res->delete();

        return ApiResponse::success();
    }

}
