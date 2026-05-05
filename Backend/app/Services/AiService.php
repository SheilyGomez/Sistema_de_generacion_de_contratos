<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Smalot\PdfParser\Parser;

class AiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config(
            "services.nanogpt.base_url",
            "https://nano-gpt.com/api",
        );
        $this->apiKey = config("services.nanogpt.api_key", "");
    }

    public function analyzeContract(string $pdfPath): string
    {
        if (!file_exists($pdfPath) || !is_readable($pdfPath)) {
            throw new InvalidArgumentException(
                "El archivo PDF no existe o no se puede leer: {$pdfPath}",
            );
        }

        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $pdfText = $pdf->getText();

        if (empty(trim($pdfText))) {
            throw new RuntimeException(
                "No se pudo extraer texto del PDF. El archivo podría estar vacío o ser una imagen escaneada.",
            );
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(300)
            ->post("{$this->baseUrl}/v1/messages", [
                "model" => "deepseek/deepseek-v4-flash",
                "max_tokens" => 4096,
                "system" =>
                    "Eres un abogado experto en revisión de contratos. Analiza el documento proporcionado y detecta cláusulas abusivas, riesgos legales, ambigüedades y cualquier aspecto que pueda perjudicar a la parte contratante. Proporciona un resumen detallado de tus hallazgos en español.",
                "messages" => [
                    [
                        "role" => "user",
                        "content" => "Texto del contrato a analizar:\n\n{$pdfText}\n\nPor favor analiza este contrato y proporciona un resumen detallado de tus hallazgos en español.",
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::error("NanoGPT analyzeContract error", [
                "status" => $response->status(),
                "body" => $response->body(),
            ]);
            throw new RuntimeException(
                "Error al analizar el contrato con la IA.",
            );
        }

        $data = $response->json();

        return $data["content"][0]["text"] ?? "No se pudo obtener el análisis.";
    }

    public function generateContract(array $requirements): array
    {
        $prompt = $this->buildGenerationPrompt($requirements);

        $response = Http::withToken($this->apiKey)
            ->timeout(300)
            ->post("{$this->baseUrl}/v1/messages", [
                "model" => "deepseek/deepseek-v4-flash",
                "max_tokens" => 8192,
                "system" =>
                    'Eres un abogado experto en redacción de contratos legales. Genera contratos profesionales, completos y legalmente sólidos en español. Incluye todas las cláusulas necesarias para proteger a ambas partes. Responde ÚNICAMENTE con un objeto JSON que contenga dos campos: "contract" (el texto completo del contrato) y "summary" (un breve resumen de lo que contiene el contrato). No incluyas formato markdown.',
                "messages" => [
                    [
                        "role" => "user",
                        "content" => [
                                "type" => "text",
                                "text" => $prompt,
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::error("NanoGPT generateContract error", [
                "status" => $response->status(),
                "body" => $response->body(),
            ]);
            throw new RuntimeException(
                "Error al generar el contrato con la IA.",
            );
        }

        $data = $response->json();
        $text = $data["content"][0]["text"] ?? "";
        $cleanText = preg_replace(
            '/^```(?:json)?\s*(.*?)\s*```$/is',
            '$1',
            trim($text),
        );

        $result = json_decode($cleanText, true);

        if (!is_array($result) || !isset($result["contract"])) {
            return [
                "contract" => $text,
                "summary" =>
                    is_array($result) && isset($result["summary"])
                        ? $result["summary"]
                        : "Contrato generado por IA.",
            ];
        }

        return $result;
    }

    private function buildGenerationPrompt(array $requirements): string
    {
        $parts = [
            "Genera un contrato legal completo en español con los siguientes datos:",
            "",
            "Tipo de servicio: " .
            ($requirements["service_type"] ?? "No especificado"),
            "Duración: " . ($requirements["duration"] ?? "No especificada"),
            "Monto de pago: " .
            ($requirements["payment_amount"] ?? "0") .
            " " .
            ($requirements["payment_currency"] ?? "USD"),
            "Nombre del cliente: " .
            ($requirements["client_name"] ?? "No especificado"),
            "Nombre del freelancer: " .
            ($requirements["freelancer_name"] ?? "No especificado"),
            "Fecha de inicio: " .
            ($requirements["start_date"] ?? "No especificada"),
        ];

        if (!empty($requirements["extra_details"])) {
            $parts[] = "";
            $parts[] = "Detalles adicionales a incluir en el contrato: {$requirements["extra_details"]}";
        }

        $parts[] = "";
        $parts[] =
            "El contrato debe incluir: objeto del contrato, obligaciones de las partes, plazo, honorarios y forma de pago, confidencialidad, propiedad intelectual, rescisión, y ley aplicable.";

        return implode("\n", $parts);
    }
}
