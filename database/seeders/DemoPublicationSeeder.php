<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemCondition;
use App\Enums\PublicationModality;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationImage;
use App\Models\User;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Publicaciones de demostración con fotografías generadas localmente (sin internet).
 * Requiere haber ejecutado antes CategorySeeder y DemoUserSeeder.
 */
class DemoPublicationSeeder extends Seeder
{
    /**
     * Color base (degradado) según la macrocategoría.
     *
     * @var array<string, array{0: array{0: int, 1: int, 2: int}, 1: array{0: int, 1: int, 2: int}}>
     */
    private const array PALETTES = [
        'ropa-calzado-y-accesorios' => [[253, 164, 175], [190, 24, 93]],
        'libros-y-material-educativo' => [[253, 224, 71], [180, 83, 9]],
        'muebles-y-decoracion' => [[253, 186, 116], [146, 64, 14]],
        'hogar-y-electrodomesticos' => [[94, 234, 212], [15, 118, 110]],
        'tecnologia-y-electronica' => [[165, 180, 252], [55, 48, 163]],
        'herramientas-y-ferreteria' => [[203, 213, 225], [51, 65, 85]],
        'deportes-y-tiempo-libre' => [[134, 239, 172], [21, 128, 61]],
        'juguetes-y-articulos-infantiles' => [[249, 168, 212], [202, 138, 4]],
    ];

    public function run(): void
    {
        if (Publication::query()->exists()) {
            return;
        }

        $users = User::query()->get()->keyBy('email');
        $admin = $users->get('admin@reusa.test');

        foreach ($this->publications() as $index => $data) {
            $category = Category::query()->where('slug', $data['category'])->with('parent')->firstOrFail();
            $owner = $users->get($data['owner'].'@reusa.test') ?? $users->first();
            $createdAt = now()->subDays($data['days'])->subMinutes($index * 7);

            $publication = new Publication([
                'category_id' => $category->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'modality' => $data['modality'],
                'condition' => $data['condition'],
                'price' => $data['price'] ?? null,
                'wanted_in_exchange' => $data['wanted'] ?? null,
                'location' => $data['location'],
            ]);
            $publication->user()->associate($owner);
            $publication->status = $data['status'] ?? PublicationStatus::Available;
            $publication->created_at = $createdAt;
            $publication->updated_at = $createdAt;

            if (isset($data['hidden'])) {
                $publication->hidden_at = now()->subDay();
                $publication->hidden_reason = $data['hidden'];
                $publication->hidden_by = $admin?->id;
            }

            $publication->save();

            $this->createImages($publication, $category, $data['images']);
        }
    }

    private function createImages(Publication $publication, Category $category, int $count): void
    {
        $macroSlug = $category->parent->slug ?? $category->slug;
        [$top, $bottom] = self::PALETTES[$macroSlug] ?? [[203, 213, 225], [51, 65, 85]];
        $disk = Storage::disk(PublicationImage::DISK);

        for ($position = 0; $position < $count; $position++) {
            $path = "publications/{$publication->id}/demo-{$position}.png";
            $disk->put($path, PlaceholderImage::png(480, 360, $top, $bottom, $publication->id * 3 + $position));
            $publication->images()->create(['path' => $path, 'position' => $position]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publications(): array
    {
        $donation = PublicationModality::Donation;
        $exchange = PublicationModality::Exchange;
        $sale = PublicationModality::Sale;
        $likeNew = ItemCondition::LikeNew;
        $good = ItemCondition::Good;
        $fair = ItemCondition::Fair;

        return [
            ['title' => 'Bicicleta de montaña rodado 26', 'description' => "Bicicleta de montaña con 21 velocidades, frenos de disco y llantas nuevas.\nLa usé poco por trabajo; funciona perfecto y viene con candado.", 'category' => 'bicicletas-y-accesorios', 'modality' => $sale, 'condition' => $good, 'price' => 280000, 'location' => 'Laureles, cerca al estadio', 'owner' => 'ana', 'days' => 1, 'images' => 3],
            ['title' => 'Escritorio de madera con cajones', 'description' => 'Escritorio de 1,20 m con tres cajones y repisa para el computador. Madera maciza, sin rayones importantes. Ideal para estudiar o teletrabajar.', 'category' => 'escritorios-y-muebles-de-oficina', 'modality' => $sale, 'condition' => $likeNew, 'price' => 150000, 'location' => 'Belén Rosales', 'owner' => 'carlos', 'days' => 2, 'images' => 2],
            ['title' => 'Colección de novelas colombianas (12 libros)', 'description' => 'Doce novelas de autores colombianos en buen estado. Las regalo completas a quien le gusten la literatura y la historia del país.', 'category' => 'literatura-y-novelas', 'modality' => $donation, 'condition' => $good, 'location' => 'El Poblado, Provenza', 'owner' => 'laura', 'days' => 3, 'images' => 2],
            ['title' => 'Chaqueta de cuero para mujer talla M', 'description' => 'Chaqueta de cuero negro, talla M, forro interno completo. Me quedó pequeña y prefiero cambiarla por otra prenda de abrigo.', 'category' => 'ropa-de-mujer', 'modality' => $exchange, 'condition' => $good, 'wanted' => 'Un abrigo o una chaqueta talla M de otro estilo', 'location' => 'Robledo, Pilarica', 'owner' => 'marcela', 'days' => 4, 'images' => 2],
            ['title' => 'Licuadora Oster de 3 velocidades', 'description' => 'Licuadora con vaso de vidrio de 1,5 litros. Funciona muy bien, solo tiene desgaste en la base. Incluye tapa y repuesto de cuchilla.', 'category' => 'electrodomesticos-pequenos', 'modality' => $sale, 'condition' => $good, 'price' => 60000, 'location' => 'Envigado, centro', 'owner' => 'marcela', 'days' => 5, 'images' => 1],
            ['title' => 'Cuna para bebé con colchón', 'description' => 'Cuna de madera con colchón ortopédico y barandas ajustables. Tiene detalles de uso, pero está firme y segura. La regalo para una familia que la necesite.', 'category' => 'muebles-de-dormitorio', 'modality' => $donation, 'condition' => $fair, 'location' => 'Laureles, Conquistadores', 'owner' => 'ana', 'days' => 6, 'images' => 3],
            ['title' => 'Portátil Lenovo ThinkPad i5 con 8 GB de RAM', 'description' => 'Portátil de oficina con procesador i5, 8 GB de RAM y disco sólido de 256 GB. Batería con buena duración. Incluye cargador original.', 'category' => 'computadores-y-accesorios', 'modality' => $sale, 'condition' => $good, 'price' => 750000, 'location' => 'El Poblado, Manila', 'owner' => 'laura', 'days' => 7, 'images' => 3, 'status' => PublicationStatus::Reserved],
            ['title' => 'Juego de ollas de acero inoxidable', 'description' => 'Set de seis ollas con tapas de vidrio, aptas para estufa de gas e inducción. Casi sin uso. Busco un electrodoméstico pequeño a cambio.', 'category' => 'menaje-de-cocina', 'modality' => $exchange, 'condition' => $likeNew, 'wanted' => 'Un horno tostador o una freidora de aire', 'location' => 'Belén, Fátima', 'owner' => 'carlos', 'days' => 8, 'images' => 2],
            ['title' => 'Balón de fútbol y guayos talla 40', 'description' => 'Balón número 5 y guayos talla 40 en buen estado. Los regalo para algún joven que quiera practicar en la cancha del barrio.', 'category' => 'equipos-deportivos', 'modality' => $donation, 'condition' => $good, 'location' => 'Robledo, Aures', 'owner' => 'jorge', 'days' => 9, 'images' => 2],
            ['title' => 'Guitarra acústica Yamaha', 'description' => 'Guitarra acústica con cuerdas nuevas y funda acolchada. Tiene un pequeño golpe en la parte trasera que no afecta el sonido.', 'category' => 'instrumentos-musicales', 'modality' => $sale, 'condition' => $good, 'price' => 220000, 'location' => 'Laureles, Segundo Parque', 'owner' => 'ana', 'days' => 10, 'images' => 3],
            ['title' => 'Libros de texto de bachillerato (grado 10)', 'description' => 'Matemáticas, física, química y sociales de grado décimo. Ya fueron entregados a un estudiante del barrio.', 'category' => 'libros-de-texto', 'modality' => $donation, 'condition' => $fair, 'location' => 'Envigado, Zona 4', 'owner' => 'marcela', 'days' => 11, 'images' => 1, 'status' => PublicationStatus::Delivered],
            ['title' => 'Taladro eléctrico Black+Decker', 'description' => 'Taladro percutor de 600 W con maletín y juego de brocas para pared y madera. Funciona sin problemas.', 'category' => 'herramientas-electricas', 'modality' => $sale, 'condition' => $good, 'price' => 90000, 'location' => 'Belén, Las Playas', 'owner' => 'carlos', 'days' => 12, 'images' => 2],
            ['title' => 'Juego de mesa Catan', 'description' => 'Juego de estrategia completo, con todas las fichas y el tablero en excelente estado. Lo cambio por otro juego para jugar en familia.', 'category' => 'juegos-de-mesa', 'modality' => $exchange, 'condition' => $likeNew, 'wanted' => 'Otro juego de mesa de estrategia o cartas', 'location' => 'El Poblado, Astorga', 'owner' => 'laura', 'days' => 13, 'images' => 2],
            ['title' => 'Vestido de fiesta talla S', 'description' => 'Vestido largo color vino, usado una sola vez. Ya fue vendido, lo dejo como ejemplo de publicación cerrada.', 'category' => 'ropa-de-mujer', 'modality' => $sale, 'condition' => $likeNew, 'price' => 70000, 'location' => 'Robledo, Córdoba', 'owner' => 'jorge', 'days' => 14, 'images' => 2, 'status' => PublicationStatus::Sold],
            ['title' => 'Lámpara de pie y cuadros decorativos', 'description' => 'Lámpara de pie con pantalla de tela y tres cuadros decorativos con marco. Los regalo porque cambié la decoración de la sala.', 'category' => 'decoracion-y-ambientacion', 'modality' => $donation, 'condition' => $good, 'location' => 'Laureles, La América', 'owner' => 'ana', 'days' => 15, 'images' => 2],
            ['title' => 'Celular Samsung Galaxy A12', 'description' => 'Celular de 64 GB con pantalla en buen estado y batería que dura todo el día. Libre para cualquier operador. Incluye cargador.', 'category' => 'celulares-y-tabletas', 'modality' => $sale, 'condition' => $fair, 'price' => 180000, 'location' => 'Envigado, Alcalá', 'owner' => 'marcela', 'days' => 16, 'images' => 1],
            ['title' => 'Juguetes didácticos de madera', 'description' => 'Bloques, rompecabezas y un tablero de formas para niños de 2 a 5 años. Están reservados para un jardín infantil del sector.', 'category' => 'juegos-didacticos', 'modality' => $donation, 'condition' => $good, 'location' => 'Belén, San Bernardo', 'owner' => 'carlos', 'days' => 17, 'images' => 3, 'status' => PublicationStatus::Reserved],
            ['title' => 'Sofá de tres puestos en tela', 'description' => 'Sofá cómodo de tres puestos en tela gris. Tiene desgaste en los brazos, pero la estructura está firme. Recogida por cuenta del comprador.', 'category' => 'muebles-de-sala-y-comedor', 'modality' => $sale, 'condition' => $fair, 'price' => 350000, 'location' => 'El Poblado, Los Balsos', 'owner' => 'laura', 'days' => 18, 'images' => 3],
            ['title' => 'Set de herramientas manuales', 'description' => 'Martillo, destornilladores, alicates, llaves y metro en una caja plástica. Busco una bicicleta infantil para mi sobrina.', 'category' => 'herramientas-manuales', 'modality' => $exchange, 'condition' => $good, 'wanted' => 'Una bicicleta infantil rodado 16 o 20', 'location' => 'Robledo, Kennedy', 'owner' => 'jorge', 'days' => 20, 'images' => 1],
            ['title' => 'Cobijas y colchoneta para camping', 'description' => 'Dos cobijas gruesas y una colchoneta inflable. Perfectas para paseos. Aún no tengo fotos, pero están en buen estado.', 'category' => 'ropa-de-cama-y-textiles-del-hogar', 'modality' => $donation, 'condition' => $good, 'location' => 'Laureles, Estadio', 'owner' => 'ana', 'days' => 22, 'images' => 0],
            ['title' => 'Audífonos con diadema', 'description' => 'Audífonos con cable y micrófono, almohadillas desgastadas pero con buen sonido. Sirven para clases virtuales.', 'category' => 'audio-y-video', 'modality' => $sale, 'condition' => $fair, 'price' => 45000, 'location' => 'Belén, La Gloria', 'owner' => 'carlos', 'days' => 24, 'images' => 1],
            ['title' => 'Alimentos enlatados próximos a vencer', 'description' => 'Publicación de ejemplo ocultada por la administración: los productos perecederos no están permitidos en ReUsa.', 'category' => 'organizadores-y-almacenamiento', 'modality' => $donation, 'condition' => $good, 'location' => 'Envigado, Primavera', 'owner' => 'marcela', 'days' => 2, 'images' => 1, 'hidden' => 'Producto no admitido: los productos perecederos no se pueden publicar.'],
        ];
    }
}
