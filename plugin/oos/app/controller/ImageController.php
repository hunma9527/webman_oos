            // 重定向到处理后的图片URL
            return redirect($url);
        } catch (ImageProcessException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '调整图片大小失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 添加水印
     * @param Request $request
     * @param int $id 文件ID
     * @return \support\Response
     */
    public function watermark(Request $request, $id)
    {
        try {
            // 检查文件是否存在
            $file = File::find($id);
            if (!$file || $file->status != 1) {
                return json(['code' => 1, 'msg' => '文件不存在或已删除']);
            }
            
            // 添加水印
            $url = $this->imageService->watermark($file);
            
            // 重定向到处理后的图片URL
            return redirect($url);
        } catch (ImageProcessException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '添加水印失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 生成缩略图
     * @param Request $request
     * @param int $id 文件ID
     * @param string $size 缩略图尺寸
     * @return \support\Response
     */
    public function thumbnail(Request $request, $id, $size = 'medium')
    {
        try {
            // 检查文件是否存在
            $file = File::find($id);
            if (!$file || $file->status != 1) {
                return json(['code' => 1, 'msg' => '文件不存在或已删除']);
            }
            
            // 生成缩略图
            $url = $this->imageService->thumbnail($file, $size);
            
            // 重定向到缩略图URL
            return redirect($url);
        } catch (ImageProcessException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '生成缩略图失败: ' . $e->getMessage()]);
        }
    }
}