/**
 * Paquetes de presentación (misma información que la landing histórica).
 * El `slug` debe coincidir con `packages.slug` en base de datos (MlmBootstrapSeeder).
 */
export const STATIC_PACKAGES_CARDS = [
  {
    slug: "fundador",
    name: "Paquete Fundador",
    priceDisplay: "Bs. 10,800",
    pvDisplay: "1200 PV · 90 Productos",
    description: "Ideal para comenzar. Acceso básico a la plataforma y plan de compensación.",
    features: ["Panel de afiliado", "Acceso a entrenamientos", "Bono directo y binario"],
    recommended: false,
    btnVariant: "outline",
  },
  {
    slug: "profesional",
    name: "Paquete Profesional",
    priceDisplay: "Bs. 5,400",
    pvDisplay: "600 PV · 45 Productos",
    description: "Para quienes desean construir un equipo sólido con mayores beneficios.",
    features: ["Todo del paquete inicial", "Bono de equipo mejorado", "Soporte prioritario"],
    recommended: true,
    btnVariant: "gradient",
  },
  {
    slug: "avanzado",
    name: "Paquete Avanzado",
    priceDisplay: "Bs. 2,700",
    pvDisplay: "300 PV · 20 Productos",
    description: "Diseñado para líderes que quieren maximizar todos los bonos disponibles.",
    features: ["Acceso a bonos de liderazgo", "Sesiones de mentoría", "Herramientas adicionales"],
    recommended: false,
    btnVariant: "gradient",
  },
  {
    slug: "basico",
    name: "Paquete Básico",
    priceDisplay: "Bs. 1,050",
    pvDisplay: "100 PV · 10 Productos",
    description: "Entrada accesible con beneficios esenciales del plan.",
    features: ["Panel de afiliado", "BIR y binario según reglas", "Escalabilidad"],
    recommended: false,
    btnVariant: "gradient",
  },
];
