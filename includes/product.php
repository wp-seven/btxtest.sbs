<?
class Product {
    public $id;
    public $name;
    public $price;
    public $article;
    public $description;
    public $url;
    public $chars = array(); 

    public function __construct(array $data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function add_char(string $section, string $name, array $values) {
        if (!isset($this->chars[$section])) {
            $this->chars[$section] = [];
        }
        $this->chars[$section][$name] = $values;
    }

    public function to_array(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'article' => $this->article,
            'description' => $this->description,
            'url' => $this->url,
            'chars' => $this->chars
        ];
    }
}

class ProductDB {
    private $db;

    public function __construct(DB $db) {
        $this->db = $db;
    }

    public function save_product(Product $product) {
        $exists = $this->db->query("SELECT id FROM products WHERE url = '" . $this->db->escape($product->url) . "'");
        if ($exists) {
            $product_id = $exists[0]['id'];
            $this->db->update("products", [
                "name" => $product->name,
                "description" => $product->description,
                "article" => $product->article,
                "price" => $product->price
                ], "id = $product_id");
                $this->db->delete('product_attribute_values', "product_id = $product_id");
        } else {
            $product_id = $this->db->insert("products", [
                "name" => $product->name,
                "description" => $product->description,
                "article" => $product->article,
                "price" => $product->price,
                "url" => $product->url
            ]);
        }
            
        foreach ($product->chars as $category_name => $attributes) {
            $exists_cat = $this->db->query("SELECT id FROM attribute_categories WHERE name = '" . $this->db->escape($category_name) . "'");
            if (!empty($exists_cat)) {
                $category_id = $exists_cat[0]['id'];
                } else {
                    $category_id = $this->db->insert('attribute_categories', ['name' => $category_name]);
                }
                    
                foreach ($attributes as $attr_name => $values) {
                    $exists_attr = $this->db->query("SELECT id FROM attributes WHERE category_id=$category_id AND name='" . $this->db->escape($attr_name) . "'");
                    if (!empty($exists_attr)) {
                        $attribute_id = $exists_attr[0]['id'];
                    } else {
                        $attribute_id = $this->db->insert('attributes', [
                            'category_id' => $category_id,
                            'name' => $attr_name
                        ]);
                    }
                        
                    foreach ($values as $val) {
                        $this->db->insert('product_attribute_values', [
                            'product_id' => $product_id,
                            'attribute_id' => $attribute_id,
                            'value' => $val
                        ]);
                    }
                }
            }
        if($exists) return 'Продукт обновлен';
        return $product_id;
    }

    public function get_product($product_id): ?Product {
        $where = is_numeric($product_id) 
        ? "id=" . intval($product_id)
        : "url='" . $this->db->escape($product_id) . "'";
        $res = $this->db->query("SELECT * FROM products WHERE $where LIMIT 1");
        if (empty($res)) return null;

        $row = $res[0];
        $product = new Product([
            'name' => $row['name'],
            'description' => $row['description'],
            'article' => $row['article'],
            'price' => $row['price'],
            'url' => $row['url'],
            'chars' => []
        ]);
        
        $product->id = $row['id'];
        
        $chars_res = $this->db->query("
            SELECT ac.name AS category_name, a.name AS attr_name, pav.value
            FROM product_attribute_values pav
            JOIN attributes a ON pav.attribute_id = a.id
            JOIN attribute_categories ac ON a.category_id = ac.id
            WHERE pav.product_id = {$product->id}
            ORDER BY ac.name, a.name");
            
        foreach ($chars_res as $c) {
            $cat = $c['category_name'];
            $attr = $c['attr_name'];
            $val = $c['value'];
            
            if (!isset($product->chars[$cat])) $product->chars[$cat] = [];
            if (!isset($product->chars[$cat][$attr])) $product->chars[$cat][$attr] = [];
            $product->chars[$cat][$attr][] = $val;
        }
        return $product;
    }
}
?>