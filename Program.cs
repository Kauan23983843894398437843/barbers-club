using Microsoft.EntityFrameworkCore;
using Microsoft.AspNetCore.Mvc;
using System.ComponentModel.DataAnnotations;


using System.ComponentModel.DataAnnotations.Schema;
using AutoMapper;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddControllers();
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen();
builder.Services.AddAutoMapper(typeof(Program));

builder.Services.AddDbContext<BarbersClubDbContext>(options =>
    options.UseMySql(
        builder.Configuration.GetConnectionString("DefaultConnection"),
        new MySqlServerVersion(new Version(8, 0, 0))
    )
);

builder.Services.AddScoped<IAgendamentoService, AgendamentoService>();
builder.Services.AddScoped<IAgendamentoAppService, AgendamentoAppService>();
builder.Services.AddScoped<IAgendamentoRepository, AgendamentoRepository>();
builder.Services.AddScoped<IBarbeiroRepository, BarbeiroRepository>();
builder.Services.AddScoped<IServicoRepository, ServicoRepository>();
builder.Services.AddScoped<IBarbeariaRepository, BarbeariaRepository>();

var app = builder.Build();

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();
app.UseAuthorization();
app.MapControllers();
app.Run();

public enum StatusAgendamento
{
    Agendado,
    Confirmado,
    Concluido,
    CanceladoCliente,
    CanceladoBarbearia
}

public class Cliente
{
    public int IdCliente { get; set; }
    public string Nome { get; set; } = string.Empty;
    public string Sobrenome { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string Senha { get; set; } = string.Empty;
    public string Telefone { get; set; } = string.Empty;
    public DateTime? DataNascimento { get; set; }
    public string? Cep { get; set; }
    public string? Estado { get; set; }
    public string? Cidade { get; set; }
    public string? Bairro { get; set; }
    public string? Endereco { get; set; }
    public string? FotoPerfil { get; set; }
    public DateTime DataCadastro { get; set; } = DateTime.UtcNow;
    public DateTime? UltimoLogin { get; set; }
    public bool Ativo { get; set; } = true;
    public virtual ICollection<Agendamento>? Agendamentos { get; set; }
    public virtual ICollection<Avaliacao>? Avaliacoes { get; set; }
    public string NomeCompleto => $"{Nome} {Sobrenome}";
}

public class Barbearia
{
    public int IdBarbearia { get; set; }
    public string Nome { get; set; } = string.Empty;
    public string Email { get; set; } = string.Empty;
    public string Senha { get; set; } = string.Empty;
    public string Telefone { get; set; } = string.Empty;
    public string? Cnpj { get; set; }
    public string Cep { get; set; } = string.Empty;
    public string Estado { get; set; } = string.Empty;
    public string Cidade { get; set; } = string.Empty;
    public string Bairro { get; set; } = string.Empty;
    public string Endereco { get; set; } = string.Empty;
    public string? Complemento { get; set; }
    public string? Descricao { get; set; }
    public string? Logo { get; set; }
    public DateTime DataCadastro { get; set; } = DateTime.UtcNow;
    public DateTime? UltimoLogin { get; set; }
    public bool Ativo { get; set; } = true;
    public bool Verificado { get; set; } = false;
    public virtual ICollection<FotoBarbearia>? Fotos { get; set; }
    public virtual ICollection<Barbeiro>? Barbeiros { get; set; }
    public virtual ICollection<Servico>? Servicos { get; set; }
    public virtual ICollection<HorarioFuncionamento>? HorariosFuncionamento { get; set; }
    public virtual ICollection<DiaFechado>? DiasFechados { get; set; }
    public virtual ICollection<Agendamento>? Agendamentos { get; set; }
    public virtual ICollection<Avaliacao>? Avaliacoes { get; set; }

    public bool EstaAberto(DateTime data, TimeSpan hora)
    {
        if (DiasFechados?.Any(d => d.Data.Date == data.Date) == true)
            return false;

        var diaSemana = (int)data.DayOfWeek;
        var horario = HorariosFuncionamento?.FirstOrDefault(h => h.DiaSemana == diaSemana);
        
        if (horario == null)
            return false;

        return hora >= horario.HoraAbertura.ToTimeSpan() && 
               hora < horario.HoraFechamento.ToTimeSpan();
    }
}

public class Barbeiro
{
    public int IdBarbeiro { get; set; }
    public int IdBarbearia { get; set; }
    public string Nome { get; set; } = string.Empty;
    public string Sobrenome { get; set; } = string.Empty;
    public string? Email { get; set; }
    public string? Telefone { get; set; }
    public string? Foto { get; set; }
    public int? AnosExperiencia { get; set; }
    public string? Especialidade { get; set; }
    public string? Biografia { get; set; }
    public bool Ativo { get; set; } = true;
    public virtual Barbearia? Barbearia { get; set; }
    public virtual ICollection<Agendamento>? Agendamentos { get; set; }
    public virtual ICollection<Avaliacao>? Avaliacoes { get; set; }
    public string NomeCompleto => $"{Nome} {Sobrenome}";
}

public class Servico
{
    public int IdServico { get; set; }
    public int IdBarbearia { get; set; }
    public int IdCategoria { get; set; }
    public string Nome { get; set; } = string.Empty;
    public string? Descricao { get; set; }
    public decimal Preco { get; set; }
    public int DuracaoMinutos { get; set; }
    public bool Ativo { get; set; } = true;
    public virtual Barbearia? Barbearia { get; set; }
    public virtual CategoriaServico? Categoria { get; set; }
    public virtual ICollection<Agendamento>? Agendamentos { get; set; }
}

public class Agendamento
{
    public int IdAgendamento { get; set; }
    public int IdCliente { get; set; }
    public int IdBarbearia { get; set; }
    public int IdBarbeiro { get; set; }
    public int IdServico { get; set; }
    public DateOnly DataAgendamento { get; set; }
    public TimeOnly HoraInicio { get; set; }
    public TimeOnly HoraFim { get; set; }
    public StatusAgendamento Status { get; set; } = StatusAgendamento.Agendado;
    public string? Observacoes { get; set; }
    public DateTime DataCriacao { get; set; } = DateTime.UtcNow;
    public DateTime? DataAtualizacao { get; set; }
    public virtual Cliente? Cliente { get; set; }
    public virtual Barbearia? Barbearia { get; set; }
    public virtual Barbeiro? Barbeiro { get; set; }
    public virtual Servico? Servico { get; set; }
    public virtual Avaliacao? Avaliacao { get; set; }

    public bool PodeSerCancelado() =>
        Status == StatusAgendamento.Agendado || Status == StatusAgendamento.Confirmado;

    public bool EstaFinalizado() =>
        Status == StatusAgendamento.Concluido || 
        Status == StatusAgendamento.CanceladoCliente || 
        Status == StatusAgendamento.CanceladoBarbearia;

    public void CancelarPeloCliente()
    {
        if (!PodeSerCancelado())
            throw new InvalidOperationException("Este agendamento não pode ser cancelado no estado atual.");
        Status = StatusAgendamento.CanceladoCliente;
        DataAtualizacao = DateTime.UtcNow;
    }

    public void CancelarPelaBarbearia()
    {
        if (!PodeSerCancelado())
            throw new InvalidOperationException("Este agendamento não pode ser cancelado no estado atual.");
        Status = StatusAgendamento.CanceladoBarbearia;
        DataAtualizacao = DateTime.UtcNow;
    }

    public void Confirmar()
    {
        if (Status != StatusAgendamento.Agendado)
            throw new InvalidOperationException("Apenas agendamentos com status 'Agendado' podem ser confirmados.");
        Status = StatusAgendamento.Confirmado;
        DataAtualizacao = DateTime.UtcNow;
    }

    public void Concluir()
    {
        if (Status != StatusAgendamento.Confirmado)
            throw new InvalidOperationException("Apenas agendamentos com status 'Confirmado' podem ser concluídos.");
        Status = StatusAgendamento.Concluido;
        DataAtualizacao = DateTime.UtcNow;
    }
}

public class AgendamentoDTO
{
    public int IdAgendamento { get; set; }
    public int IdCliente { get; set; }
    public string NomeCliente { get; set; } = string.Empty;
    public int IdBarbearia { get; set; }
    public string NomeBarbearia { get; set; } = string.Empty;
    public int IdBarbeiro { get; set; }
    public string NomeBarbeiro { get; set; } = string.Empty;
    public int IdServico { get; set; }
    public string NomeServico { get; set; } = string.Empty;
    public decimal PrecoServico { get; set; }
    public string DataAgendamento { get; set; } = string.Empty;
    public string HoraInicio { get; set; } = string.Empty;
    public string HoraFim { get; set; } = string.Empty;
    public string Status { get; set; } = string.Empty;
    public string? Observacoes { get; set; }
    public bool PodeSerCancelado { get; set; }
    public bool PodeSerConfirmado { get; set; }
    public bool PodeSerConcluido { get; set; }
    public bool PodeSerAvaliado { get; set; }
}

public class CriarAgendamentoDTO
{
    public int IdCliente { get; set; }
    public int IdBarbearia { get; set; }
    public int IdBarbeiro { get; set; }
    public int IdServico { get; set; }
    public string DataAgendamento { get; set; } = string.Empty;
    public string HoraInicio { get; set; } = string.Empty;
    public string? Observacoes { get; set; }
}

[ApiController]
[Route("api/[controller]")]
public class AgendamentoController : ControllerBase
{
    private readonly IAgendamentoAppService _agendamentoService;
    private readonly ILogger<AgendamentoController> _logger;

    public AgendamentoController(
        IAgendamentoAppService agendamentoService,
        ILogger<AgendamentoController> logger)
    {
        _agendamentoService = agendamentoService;
        _logger = logger;
    }

    [HttpPost]
    public async Task<ActionResult<AgendamentoDTO>> CriarAgendamento([FromBody] CriarAgendamentoDTO dto)
    {
        try
        {
            var agendamento = await _agendamentoService.CriarAgendamentoAsync(dto);
            return CreatedAtAction(nameof(ObterAgendamento), new { id = agendamento.IdAgendamento }, agendamento);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Erro ao criar agendamento");
            return StatusCode(500, "Ocorreu um erro ao processar sua solicitação.");
        }
    }

    [HttpGet("{id}")]
    public async Task<ActionResult<AgendamentoDTO>> ObterAgendamento(int id)
    {
        try
        {
            var agendamento = await _agendamentoService.ObterAgendamentoAsync(id);
            return Ok(agendamento);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Erro ao obter agendamento");
            return StatusCode(500, "Ocorreu um erro ao processar sua solicitação.");
        }
    }

    [HttpGet("cliente/{idCliente}")]
    public async Task<ActionResult<IEnumerable<AgendamentoDTO>>> ObterAgendamentosCliente(
        int idCliente, 
        [FromQuery] bool incluirFinalizados = false)
    {
        try
        {
            var agendamentos = await _agendamentoService.ObterAgendamentosClienteAsync(idCliente, incluirFinalizados);
            return Ok(agendamentos);
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Erro ao obter agendamentos do cliente");
            return StatusCode(500, "Ocorreu um erro ao processar sua solicitação.");
        }
    }

    [HttpPut("{id}/confirmar")]
    public async Task<ActionResult> ConfirmarAgendamento(int id)
    {
        try
        {
            await _agendamentoService.ConfirmarAgendamentoAsync(id);
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Erro ao confirmar agendamento");
            return StatusCode(500, "Ocorreu um erro ao processar sua solicitação.");
        }
    }

    [HttpPut("{id}/cancelar")]
    public async Task<ActionResult> CancelarAgendamento(int id)
    {
        try
        {
            await _agendamentoService.CancelarAgendamentoClienteAsync(id);
            return NoContent();
        }
        catch (Exception ex)
        {
            _logger.LogError(ex, "Erro ao cancelar agendamento");
            return StatusCode(500, "Ocorreu um erro ao processar sua solicitação.");
        }
    }
}

public interface IAgendamentoAppService
{
    Task<AgendamentoDTO> CriarAgendamentoAsync(CriarAgendamentoDTO dto);
    Task<AgendamentoDTO> ObterAgendamentoAsync(int idAgendamento);
    Task<IEnumerable<AgendamentoDTO>> ObterAgendamentosClienteAsync(int idCliente, bool incluirFinalizados = false);
    Task ConfirmarAgendamentoAsync(int idAgendamento);
    Task CancelarAgendamentoClienteAsync(int idAgendamento);
}

public class AgendamentoAppService : IAgendamentoAppService
{
    private readonly IAgendamentoService _agendamentoService;
    private readonly IMapper _mapper;

    public AgendamentoAppService(IAgendamentoService agendamentoService, IMapper mapper)
    {
        _agendamentoService = agendamentoService;
        _mapper = mapper;
    }

    public async Task<AgendamentoDTO> CriarAgendamentoAsync(CriarAgendamentoDTO dto)
    {
        var dataAgendamento = DateOnly.Parse(dto.DataAgendamento);
        var horaInicio = TimeOnly.Parse(dto.HoraInicio);

        var agendamento = new Agendamento
        {
            IdCliente = dto.IdCliente,
            IdBarbearia = dto.IdBarbearia,
            IdBarbeiro = dto.IdBarbeiro,
            IdServico = dto.IdServico,
            DataAgendamento = dataAgendamento,
            HoraInicio = horaInicio,
            Observacoes = dto.Observacoes
        };

        var resultado = await _agendamentoService.CriarAgendamentoAsync(agendamento);
        return _mapper.Map<AgendamentoDTO>(resultado);
    }

    public async Task<AgendamentoDTO> ObterAgendamentoAsync(int idAgendamento)
    {
        var agendamento = await _agendamentoService.ObterAgendamentoAsync(idAgendamento);
        return _mapper.Map<AgendamentoDTO>(agendamento);
    }

    public async Task<IEnumerable<AgendamentoDTO>> ObterAgendamentosClienteAsync(int idCliente, bool incluirFinalizados = false)
    {
        var agendamentos = await _agendamentoService.ObterAgendamentosClienteAsync(idCliente, incluirFinalizados);
        return _mapper.Map<IEnumerable<AgendamentoDTO>>(agendamentos);
    }

    public async Task ConfirmarAgendamentoAsync(int idAgendamento)
    {
        await _agendamentoService.ConfirmarAgendamentoAsync(idAgendamento);
    }

    public async Task CancelarAgendamentoClienteAsync(int idAgendamento)
    {
        await _agendamentoService.CancelarAgendamentoClienteAsync(idAgendamento);
    }
}

public class MappingProfile : Profile
{
    public MappingProfile()
    {
        CreateMap<Agendamento, AgendamentoDTO>()
            .ForMember(dest => dest.NomeCliente, opt => opt.MapFrom(src => 
                src.Cliente != null ? src.Cliente.NomeCompleto : string.Empty))
            .ForMember(dest => dest.NomeBarbearia, opt => opt.MapFrom(src => 
                src.Barbearia != null ? src.Barbearia.Nome : string.Empty))
            .ForMember(dest => dest.NomeBarbeiro, opt => opt.MapFrom(src => 
                src.Barbeiro != null ? src.Barbeiro.NomeCompleto : string.Empty))
            .ForMember(dest => dest.NomeServico, opt => opt.MapFrom(src => 
                src.Servico != null ? src.Servico.Nome : string.Empty))
            .ForMember(dest => dest.PrecoServico, opt => opt.MapFrom(src => 
                src.Servico != null ? src.Servico.Preco : 0))
            .ForMember(dest => dest.DataAgendamento, opt => opt.MapFrom(src => 
                src.DataAgendamento.ToString("yyyy-MM-dd")))
            .ForMember(dest => dest.HoraInicio, opt => opt.MapFrom(src => 
                src.HoraInicio.ToString("HH:mm")))
            .ForMember(dest => dest.HoraFim, opt => opt.MapFrom(src => 
                src.HoraFim.ToString("HH:mm")))
            .ForMember(dest => dest.Status, opt => opt.MapFrom(src => 
                src.Status.ToString()))
            .ForMember(dest => dest.PodeSerCancelado, opt => opt.MapFrom(src => 
                src.PodeSerCancelado()))
            .ForMember(dest => dest.PodeSerConfirmado, opt => opt.MapFrom(src => 
                src.Status == StatusAgendamento.Agendado))
            .ForMember(dest => dest.PodeSerConcluido, opt => opt.MapFrom(src => 
                src.Status == StatusAgendamento.Confirmado))
            .ForMember(dest => dest.PodeSerAvaliado, opt => opt.MapFrom(src => 
                src.Status == StatusAgendamento.Concluido && src.Avaliacao == null));
    }
}